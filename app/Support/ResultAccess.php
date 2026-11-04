<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gerbang password bersama untuk Klasemen & Hasil Cabang.
 *
 * Satu password / satu token / satu flag "wajib password" (tabel settings):
 *  - standing_using_password = 1  -> wajib header X-Standings-Token
 *  - standing_password            -> password yang diminta ke pengunjung
 *
 * Flag "standing_published" TIDAK dipakai di sini: itu khusus Klasemen.
 * Hasil Cabang tetap memakai status rilis per cabang (event_snapshots).
 */
class ResultAccess
{
    public const HEADER = 'X-Standings-Token';

    /** Default aman: wajib password bila setting belum ada. */
    public static function settings(): array
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
    public static function token(string $password): string
    {
        return hash_hmac('sha256', 'standings|' . $password, (string) config('app.key'));
    }

    public static function hasValidToken(Request $request, string $password): bool
    {
        if ($password === '') {
            return false;
        }

        $given = (string) $request->header(self::HEADER, '');

        return $given !== '' && hash_equals(self::token($password), $given);
    }

    public static function lockedResponse(): JsonResponse
    {
        return response()->json([
            'success'           => false,
            'code'              => 'locked',
            'message'           => 'Masukkan password untuk membuka halaman ini.',
            'requires_password' => true,
            'data'              => [],
        ], 401);
    }
}