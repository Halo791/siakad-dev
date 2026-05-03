<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlokirMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'blokir_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'tipe_blokir',
        'status',
        'alasan',
        'dibuat_oleh',
        'dicabut_oleh',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function releaser()
    {
        return $this->belongsTo(User::class, 'dicabut_oleh');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            });
    }
}
