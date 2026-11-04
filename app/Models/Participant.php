<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Participant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'participants';

    protected $guarded = [];

    protected $casts = [
        'date_of_birth'       => 'date:Y-m-d',
        'tanggal_terbit_ktp'  => 'date:Y-m-d',
        'tanggal_terbit_kk'   => 'date:Y-m-d',
    ];

    protected $appends = ['lampiran_completion_percent'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    /* ============================
     *  RELATIONSHIPS
     * ============================
     */

    // wilayah
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    // relasi ke tabel pivot event_participants
    public function eventParticipants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    // many-to-many: participant ↔ event melalui event_participants
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->using(EventParticipant::class)
            ->withPivot([
                'event_competition_branch_id',
                'age_year',
                'age_month',
                'age_day',
                'status_pendaftaran',
                'registration_notes',
                'moved_by',
                'verified_by',
                'verified_at',
                'created_at',
                'updated_at',
            ]);
    }

    /* ============================
     *  ACCESSOR / HELPER
     * ============================
     */

    public function getFullAddressAttribute()
    {
        $parts = [
            $this->address,
            optional($this->village)->name,
            optional($this->district)->name,
            optional($this->regency)->name,
            optional($this->province)->name,
        ];

        return trim(collect($parts)->filter()->implode(', '));
    }

    // ============================
    // ACCESSORS → AUTO /secure/*
    // ============================

    protected function secureUrl($value)
    {
        if (empty($value)) return null;

        // Jika sudah URL absolut (http / https), return langsung
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        // Path relatif file di project ADMIN (route secure.docs.stream)
        $path = !empty($this->uuid)
            ? '/secure/documents/' . rawurlencode($this->uuid) . '/' . rawurlencode(basename($value))
            : '/secure/documents/not-found';

        return $this->adminBaseUrl() . $path;
    }

    /**
     * Base URL project admin, tempat file disimpan dan di-stream.
     *  - Project admin : ADMIN_URL kosong -> pakai host project ini (APP_URL / request).
     *  - Project public: isi ADMIN_URL di .env, mis. http://127.0.0.1:8001 (lokal)
     *                    atau https://admin.domainanda.id (production).
     */
    protected function adminBaseUrl(): string
    {
        $base = config('app.admin_url');

        return $base ? rtrim($base, '/') : rtrim(url('/'), '/');
    }

    public function getPhotoUrlAttribute($value)
    {
        return $this->secureUrl($value);
    }

    public function getIdCardUrlAttribute($value)
    {
        return $this->secureUrl($value);
    }

    public function getFamilyCardUrlAttribute($value)
    {
        return $this->secureUrl($value);
    }

    public function getBankBookUrlAttribute($value)
    {
        return $this->secureUrl($value);
    }

    public function getCertificateUrlAttribute($value)
    {
        return $this->secureUrl($value);
    }

    public function getOtherUrlAttribute($value)
    {
        return $this->secureUrl($value);
    }

    /**
     * Persentase kelengkapan lampiran (0–100)
     * Kategori umur >= 17 tahun (4 wajib: Foto, KTP, Akta, KK)
     * Kategori umur < 17 tahun (3 wajib: Foto, Akta, KK)
     */
    public function getLampiranCompletionPercentAttribute(): int
    {
        // Hitung umur (default 0 jika tanggal lahir kosong)
        $age = 0;
        if (!empty($this->date_of_birth)) {
            $age = Carbon::parse($this->date_of_birth)->age;
        }

        // Tentukan field wajib berdasarkan umur
        if ($age >= 17) {
            $fields = [
                'photo_url',
                'id_card_url', // KTP
                'other_url', // Akta Kelahiran
                'family_card_url', // KK
            ];
        } else {
            $fields = [
                'photo_url',
                'other_url', // Akta Kelahiran
                'family_card_url', // KK
            ];
        }

        $total  = count($fields);
        $filled = 0;

        foreach ($fields as $field) {
            // pakai nilai asli di DB, bukan accessor /secure/*
            $raw = $this->getRawOriginal($field);
            if (!empty($raw)) {
                $filled++;
            }
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($filled / $total) * 100);
    }

    public function verifications()
    {
        return $this->hasMany(ParticipantVerification::class);
    }

    public function latestVerification()
    {
        return $this->hasOne(ParticipantVerification::class)->latestOfMany();
    }
}