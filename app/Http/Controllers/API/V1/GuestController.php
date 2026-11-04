<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    public function getGuestSnapshot(): JsonResponse
    {
        // Ambil snapshot terbaru dengan tipe 'guest'
        $snapshot = EventSnapshot::where('type', 'guest')
            ->whereNotNull('published_at')
            ->latest()
            ->first();

        // Jika snapshot belum ada, buat secara otomatis dari data master event
        if (!$snapshot) {
            // Sesuaikan dengan event_key yang di-insert di database ('MTQXLIPSL')
            $event = Event::where('event_key', 'MTQXLIIPSL')->first();

            if (!$event) {
                return response()->json(['message' => 'Data event utama tidak ditemukan.'], 404);
            }

            $snapshot = EventSnapshot::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'type'     => 'guest',
                ],
                [
                    'payload' => [
                        'event_key'        => $event->event_key,
                        'app_name'         => $event->app_name,
                        'event_name'       => $event->event_name,
                        'event_year'       => $event->event_year,
                        'event_location'   => $event->event_location,
                        'event_tagline'    => $event->event_tagline,
                        'start_date'       => $event->start_date,
                        'end_date'         => $event->end_date,
                        'logo_event'       => $event->logo_event,
                        'logo_sponsor_1'   => $event->logo_sponsor_1,
                        'logo_sponsor_2'   => $event->logo_sponsor_2,
                        'logo_sponsor_3'   => $event->logo_sponsor_3,
                    ],
                    'published_at' => now(),
                ]
            );
        }

        // Kembalikan array payload ke frontend Vue
        return response()->json($snapshot->payload);
    }

    /**
     * Klasemen juara umum untuk halaman publik.
     * HANYA membaca snapshot 'leaderboard' (tanpa live query).
     *
     * Akses diatur oleh tabel settings:
     *  - standing_published = 0            -> 403 (code: not_published)
     *  - standing_using_password = 1       -> wajib header X-Standings-Token, jika tidak 401 (code: locked)
     */
    public function getGuestStandings(Request $request): JsonResponse
    {
        $cfg = $this->standingSettings();

        if (!$cfg['published']) {
            return response()->json([
                'success'           => false,
                'code'              => 'not_published',
                'message'           => 'Klasemen belum dipublikasikan.',
                'requires_password' => $cfg['using_password'],
                'data'              => [],
            ], 403);
        }

        if ($cfg['using_password'] && !$this->hasValidStandingToken($request, $cfg['password'])) {
            return response()->json([
                'success'           => false,
                'code'              => 'locked',
                'message'           => 'Masukkan password untuk membuka klasemen.',
                'requires_password' => true,
                'data'              => [],
            ], 401);
        }

        $eventId = $this->resolveEventId();

        if (!$eventId) {
            return response()->json([
                'success' => false,
                'message' => 'Data event utama tidak ditemukan.',
                'data'    => [],
            ], 404);
        }

        $snapshot = EventSnapshot::where([
            'event_id' => $eventId,
            'type'     => 'leaderboard',
        ])->latest('published_at')->first();

        return response()->json([
            'success'           => true,
            'source'            => 'snapshot',
            'requires_password' => $cfg['using_password'],
            'published_at'      => $snapshot?->published_at,
            'data'              => $snapshot?->payload ?? [],
        ]);
    }

    /**
     * Verifikasi password klasemen. Jika benar, kembalikan token akses.
     * Pasang throttle pada route ini (mis. throttle:5,1) agar password tidak bisa ditebak.
     */
    public function unlockStandings(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);

        $cfg = $this->standingSettings();

        if (!$cfg['published']) {
            return response()->json([
                'success' => false,
                'code'    => 'not_published',
                'message' => 'Klasemen belum dipublikasikan.',
            ], 403);
        }

        // Gerbang password dimatikan -> tidak perlu token
        if (!$cfg['using_password']) {
            return response()->json(['success' => true, 'requires_password' => false, 'token' => null]);
        }

        // Password di server kosong -> tolak semua (hindari akses karena salah konfigurasi)
        if ($cfg['password'] === '' || !hash_equals($cfg['password'], (string) $request->input('password'))) {
            return response()->json([
                'message' => 'Password salah.',
                'errors'  => ['password' => ['Password salah.']],
            ], 422);
        }

        return response()->json([
            'success'           => true,
            'requires_password' => true,
            'token'             => $this->standingToken($cfg['password']),
        ]);
    }

    /**
     * Baca pengaturan klasemen dari tabel settings.
     * Default aman: tidak dipublikasikan & wajib password.
     */
    private function standingSettings(): array
    {
        $s = DB::table('settings')
            ->whereIn('key', ['standing_published', 'standing_using_password', 'standing_password'])
            ->pluck('value', 'key');

        return [
            'published'      => filter_var($s['standing_published'] ?? '0', FILTER_VALIDATE_BOOLEAN),
            'using_password' => filter_var($s['standing_using_password'] ?? '1', FILTER_VALIDATE_BOOLEAN),
            'password'       => (string) ($s['standing_password'] ?? ''),
        ];
    }

    /**
     * Token diturunkan dari password + APP_KEY: tidak membocorkan password,
     * dan otomatis tidak valid begitu password diganti di pengaturan.
     */
    private function standingToken(string $password): string
    {
        return hash_hmac('sha256', 'standings|' . $password, (string) config('app.key'));
    }

    private function hasValidStandingToken(Request $request, string $password): bool
    {
        if ($password === '') {
            return false;
        }

        $given = (string) $request->header('X-Standings-Token', '');

        return $given !== '' && hash_equals($this->standingToken($password), $given);
    }

    /**
     * Tentukan event aktif: ikuti snapshot guest terbaru, fallback ke event_key.
     */
    private function resolveEventId(): ?int
    {
        $guest = EventSnapshot::where('type', 'guest')
            ->whereNotNull('published_at')
            ->latest()
            ->first();

        if ($guest) {
            return (int) $guest->event_id;
        }

        return Event::where('event_key', 'MTQXLIIPSL')->value('id');
    }
}