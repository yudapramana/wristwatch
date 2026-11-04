<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Models\EventCompetition;
use App\Models\EventGroup;
use App\Models\EventSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Hasil per cabang (leaderboard) untuk halaman publik.
 *
 * Akses memakai pengaturan & token yang SAMA dengan Klasemen (GuestController::getGuestStandings),
 * jadi satu password membuka dua halaman: standing_published, standing_using_password, standing_password.
 *
 * Sumber data: tabel event_snapshots dengan type
 *   ranking_group_{event_group_id}_cat_{event_category_id|all}
 * yang ditulis oleh EventMedalStandingController::publish().
 *
 * Bentuk payload (lihat publish()):
 *   competition_id, event_group_id, event_category_id, is_team,
 *   ranking[] (sudah diurutkan, rank 1-3 = Final, rank 4-6 = Harapan)
 *
 * Isi tiap baris ranking:
 *   - Individu (buildRankingIndividu):
 *       event_participant_id, full_name, photo_url, participant_number,
 *       contingent, event_category{id,full_name}, final_score, rank
 *   - Tim (buildRankingGroupedByContingentCategory):
 *       group_key, contingent, members[{full_name,photo_url,participant_number,..}],
 *       member_names[], member_count, event_category{id,full_name}, final_score, rank
 */
class GuestResultController extends Controller
{
    private const TYPE_PREFIX = 'ranking_group_';

    /**
     * GET /api/v1/guest/results?event_id=1
     * Daftar cabang (event_groups) + kategori + status rilis, untuk sidebar.
     */
    public function index(Request $request)
    {
        $cfg = $this->standingSettings();
        if ($denied = $this->guard($request, $cfg)) {
            return $denied;
        }

        $eventId = $request->integer('event_id', 1);

        // type => published_at, lalu dikelompokkan per event_group_id
        $snapshots = EventSnapshot::query()
            ->where('event_id', $eventId)
            ->where('type', 'like', self::TYPE_PREFIX . '%')
            ->get(['type', 'published_at']);

        $releasedAt = [];
        foreach ($snapshots as $snap) {
            if (!preg_match('/^ranking_group_(\d+)_cat_/', $snap->type, $m)) continue;
            $gid  = (int) $m[1];
            $time = $snap->published_at;
            if (!isset($releasedAt[$gid]) || ($time && $time > $releasedAt[$gid])) {
                $releasedAt[$gid] = $time;
            }
        }

        $eventGroups = EventGroup::query()
            ->where('event_id', $eventId)
            ->where('status', 'active')
            ->orderByRaw('COALESCE(order_number, 999999)')
            ->orderBy('id')
            ->get();

        $categoriesByGroup = $this->categoriesFor($eventId);

        $groups = $eventGroups->map(function (EventGroup $g) use ($releasedAt, $categoriesByGroup) {
            $released = array_key_exists($g->id, $releasedAt);

            return [
                'id'           => $g->id,
                'code'         => $g->code ?: $this->initials($g->branch_name),
                'name'         => $g->full_name,
                'is_team'      => (bool) $g->is_team,
                'status'       => $released ? 'released' : 'pending',
                'published_at' => $released ? optional($releasedAt[$g->id])->toIso8601String() : null,
                'categories'   => ($categoriesByGroup[$this->groupKey($g)] ?? collect())
                    ->map(fn ($c) => ['id' => $c->id, 'name' => $this->categoryLabel($c)])
                    ->values(),
            ];
        })->values();

        return response()->json([
            'requires_password' => $cfg['using_password'],
            'data' => $groups,
            'meta' => [
                'total'    => $groups->count(),
                'released' => $groups->where('status', 'released')->count(),
                'pending'  => $groups->where('status', 'pending')->count(),
            ],
        ]);
    }

    /**
     * GET /api/v1/guest/results/{eventGroup}?event_id=1
     * Hasil satu cabang, dipisah per kategori (mis. Putra / Putri).
     */
    public function show(Request $request, int $eventGroupId)
    {
        if ($denied = $this->guard($request, $this->standingSettings())) {
            return $denied;
        }

        $eventId = $request->integer('event_id', 1);

        $group = EventGroup::where('event_id', $eventId)->findOrFail($eventGroupId);

        $snapshots = EventSnapshot::query()
            ->where('event_id', $eventId)
            ->where('type', 'like', self::TYPE_PREFIX . $group->id . '_cat_%')
            ->get();

        if ($snapshots->isEmpty()) {
            return response()->json([
                'group'      => $this->groupMeta($group),
                'status'     => 'pending',
                'categories' => [],
            ]);
        }

        // Nama babak dari kompetisi yang dipublikasikan
        $compIds = $snapshots->map(fn ($s) => $s->payload['competition_id'] ?? null)->filter()->unique();
        $roundNames = EventCompetition::with('round:id,name')
            ->whereIn('id', $compIds)
            ->get()
            ->map(fn ($c) => $c->round?->name)
            ->filter()->unique()->values();

        $categoryNames = ($this->categoriesFor($eventId)[$this->groupKey($group)] ?? collect())
            ->mapWithKeys(fn ($c) => [$c->id => $this->categoryLabel($c)]);

        $categories = $snapshots->map(function ($snap) use ($categoryNames) {
            $payload = $snap->payload;
            $isTeam  = (bool) ($payload['is_team'] ?? false);
            $catId   = $payload['event_category_id'] ?? null;

            $rows = collect($payload['ranking'] ?? [])
                ->sortBy('rank')
                ->values()
                ->map(fn ($r) => $isTeam ? $this->mapTeamRow($r) : $this->mapIndividualRow($r));

            $name = $categoryNames[$catId]
                ?? ($payload['ranking'][0]['event_category']['full_name'] ?? null)
                ?? 'Semua peserta';

            return [
                'id'           => $catId,
                'name'         => $name,
                'is_team'      => $isTeam,
                'published_at' => optional($snap->published_at)->toIso8601String(),
                'total'        => $rows->count(),
                'ranking'      => $rows,
            ];
        })->sortBy('id')->values();

        return response()->json([
            'group'        => $this->groupMeta($group),
            'status'       => 'released',
            'round'        => $roundNames->implode(' / ') ?: 'Final',
            'published_at' => optional($snapshots->max('published_at'))->toIso8601String(),
            'categories'   => $categories,
        ]);
    }

    /**
     * Verifikasi password (sama dengan GuestController::unlockStandings). Jika benar, kembalikan token akses.
     * Pasang throttle pada route ini (mis. throttle:5,1) agar password tidak bisa ditebak.
     * POST /api/v1/guest/results/unlock
     */
    public function unlock(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);

        $cfg = $this->standingSettings();

        if (!$cfg['published']) {
            return response()->json([
                'success' => false,
                'code'    => 'not_published',
                'message' => 'Hasil belum dipublikasikan.',
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
     * Gerbang akses (sama dengan pengecekan di getGuestStandings).
     * Mengembalikan response penolakan, atau null bila boleh lanjut.
     *  - standing_published = 0       -> 403 (code: not_published)
     *  - standing_using_password = 1  -> wajib header X-Standings-Token, jika tidak 401 (code: locked)
     */
    private function guard(Request $request, array $cfg): ?JsonResponse
    {
        if (!$cfg['published']) {
            return response()->json([
                'success'           => false,
                'code'              => 'not_published',
                'message'           => 'Hasil belum dipublikasikan.',
                'requires_password' => $cfg['using_password'],
                'data'              => [],
            ], 403);
        }

        if ($cfg['using_password'] && !$this->hasValidStandingToken($request, $cfg['password'])) {
            return response()->json([
                'success'           => false,
                'code'              => 'locked',
                'message'           => 'Masukkan password untuk membuka hasil cabang.',
                'requires_password' => true,
                'data'              => [],
            ], 401);
        }

        return null;
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
     * Token diturunkan dari password + APP_KEY (HARUS sama dengan GuestController::standingToken,
     * agar token dari halaman Klasemen juga berlaku di sini).
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

    // ------------------------------------------------------------------

    /**
     * Kategori per cabang, dikelompokkan dengan kunci event_id|branch_id|group_id.
     * Tidak memakai EventGroup::categories() lewat with(): relasi itu memakai whereColumn
     * ke event_groups sehingga hanya valid untuk lazy load, bukan eager load.
     */
    private function categoriesFor(int $eventId)
    {
        return EventCategory::query()
            ->where('event_id', $eventId)
            ->where('status', 'active')
            ->orderByRaw('COALESCE(order_number, 999999)')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($c) => "{$c->event_id}|{$c->branch_id}|{$c->group_id}");
    }

    private function groupKey(EventGroup $g): string
    {
        return "{$g->event_id}|{$g->branch_id}|{$g->group_id}";
    }

    /** Judul kolom: pakai category_name (mis. Putra/Putri), cadangan full_name. */
    private function categoryLabel(EventCategory $c): string
    {
        return $c->category_name ?: $c->full_name;
    }

    private function mapIndividualRow(array $r): array
    {
        return [
            'rank'               => (int) $r['rank'],
            'medal'              => $this->medalLabel((int) $r['rank']),
            'name'               => $r['full_name'] ?? '-',
            'contingent'         => $r['contingent'] ?? '-',
            'participant_number' => $r['participant_number'] ?? null,
            'photo_url'          => $this->photoUrl($r['photo_url'] ?? null),
            'final_score'        => isset($r['final_score']) ? round((float) $r['final_score'], 2) : null,
        ];
    }

    private function mapTeamRow(array $r): array
    {
        $members = collect($r['members'] ?? [])->map(fn ($m) => [
            'name'               => $m['full_name'] ?? '-',
            'participant_number' => $m['participant_number'] ?? null,
            'photo_url'          => $this->photoUrl($m['photo_url'] ?? null),
        ])->values();

        return [
            'rank'               => (int) $r['rank'],
            'medal'              => $this->medalLabel((int) $r['rank']),
            'name'               => $r['contingent'] ?? '-',   // tim dikenali dari kontingennya
            'contingent'         => $r['contingent'] ?? '-',
            'members'            => $members,
            'member_count'       => $r['member_count'] ?? $members->count(),
            'participant_number' => $members->pluck('participant_number')->filter()->implode(', ') ?: null,
            'photo_url'          => $members->first()['photo_url'] ?? null,
            'final_score'        => isset($r['final_score']) ? round((float) $r['final_score'], 2) : null,
        ];
    }

    /** Rank 1-3 = Juara; rank 4-6 = Juara Harapan 1-3 (sesuai publish()). */
    private function medalLabel(int $rank): string
    {
        return $rank <= 3 ? "Juara {$rank}" : 'Juara Harapan ' . ($rank - 3);
    }

    /**
     * Normalisasi photo_url dari snapshot.
     * Snapshot menyimpan URL absolut yang host-nya terbentuk saat publish di admin.
     * Untuk path /secure/documents/..., host diganti dengan config('app.admin_url')
     * (ADMIN_URL di .env), sehingga snapshot lama pun ikut benar. URL lain dibiarkan.
     */
    private function photoUrl(?string $raw): ?string
    {
        if (!$raw) return null;

        $adminUrl = rtrim((string) config('app.admin_url'), '/');
        $isSecure = fn (string $path) => str_starts_with($path, '/secure/documents/');

        if (preg_match('#^(https?:)?//#', $raw)) {
            $path  = parse_url($raw, PHP_URL_PATH) ?: '';
            $query = parse_url($raw, PHP_URL_QUERY);

            return ($adminUrl && $isSecure($path))
                ? $adminUrl . $path . ($query ? '?' . $query : '')
                : $raw;
        }

        if (str_starts_with($raw, '/')) {
            return ($adminUrl && $isSecure($raw)) ? $adminUrl . $raw : $raw;
        }

        return Storage::url($raw);
    }

    private function groupMeta(EventGroup $g): array
    {
        return [
            'id'      => $g->id,
            'code'    => $g->code ?: $this->initials($g->branch_name),
            'name'    => $g->full_name,
            'is_team' => (bool) $g->is_team,
        ];
    }

    private function initials(?string $name): string
    {
        $letters = collect(preg_split('/\s+/', trim((string) $name)))
            ->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

        return mb_substr($letters, 0, 3) ?: '-';
    }
}