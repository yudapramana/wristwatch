<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class __BranchSeeder extends Seeder
{
    public function run()
    {
        // Base array sesuai permintaan (tidak diubah)
        $competitionGroups = [
            'Fahm Al Qur\'an',
            'Hafalan Al Qur\'an',
            'Karya Tulis Ilmiah Al Qur\'an (KTIQ)',
            'Karya Tulis Ilmiah Hadits (KTIH)',
            'Khutbah Jum\'at & Adzan',
            'Kitab Standar',
            'Musabaqah Hafalan Hadits Nabi',
            'Qiraat Al Qur\'an',
            'Seni Baca Al Qur\'an (Tilawah)',
            'Seni Kaligrafi Al Qur\'an',
            'Syarhil Qur\'an',
            'Tafsir Al Qur\'an',
            'Tartil Al Qur\'an',
            'Tartil Al Qur\'an Eksekutif (Eselon II)',
        ];

        // Cabang yang merupakan beregu
        $teamBranches = [
            'fahm al qur\'an',
            'khutbah jum\'at & adzan',
            'syarhil qur\'an',
        ];

        // Mapping kode manual agar 100% akurat dengan gambar image_5ac725.jpg (tanpa penomoran/titik)
        $codeMap = [
            'Fahm Al Qur\'an'                          => 'FQ',
            'Hafalan Al Qur\'an'                      => 'HQ',  // Sesuai Hifzil di gambar
            'Karya Tulis Ilmiah Al Qur\'an (KTIQ)'    => 'KIQ', // Sesuai gambar
            'Karya Tulis Ilmiah Hadits (KTIH)'        => 'KIH', // Sesuai gambar
            'Khutbah Jum\'at & Adzan'                 => 'KJ',  // Sesuai gambar
            'Kitab Standar'                           => 'KS',  // Sesuai gambar (KS 1 -> KS)
            'Musabaqah Hafalan Hadits Nabi'           => 'MH',  // Ekstra: Format disamakan (karena tidak ada di gambar)
            'Qiraat Al Qur\'an'                       => 'QR',  // Ekstra: Format disamakan (karena tidak ada di gambar)
            'Seni Baca Al Qur\'an (Tilawah)'          => 'TL',  // Sesuai Tilawah di gambar
            'Seni Kaligrafi Al Qur\'an'               => 'KQ',  // Sesuai Khatil di gambar
            'Syarhil Qur\'an'                         => 'SQ',  // Sesuai gambar
            'Tafsir Al Qur\'an'                       => 'TF',  // Sesuai gambar
            'Tartil Al Qur\'an'                       => 'TT',  // Sesuai gambar
            'Tartil Al Qur\'an Eksekutif (Eselon II)' => 'TE',  // Sesuai kelompok Tartil
        ];

        $order = 1;

        foreach ($competitionGroups as $originalName) {

            $nameToSave = $originalName;

            // Ambil kode dari array mapping, gunakan 'XX' sebagai default jaga-jaga
            $code = $codeMap[$originalName] ?? 'XX';

            /* ============================
               Tentukan is_team
            ============================ */
            $normalizedName = strtolower(trim($originalName));
            $isTeam = in_array($normalizedName, $teamBranches);

            /* ============================
               SIMPAN KE DATABASE
            ============================ */
            DB::table('branches')->insert([
                'code'         => $code,
                'name'         => $nameToSave,
                'order_number' => $order,
                'is_team'      => $isTeam,
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $order++;
        }
    }
}