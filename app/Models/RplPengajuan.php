<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RplPengajuan extends Model
{
    use HasFactory;

    protected $table = 'rpl_pengajuan';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'judul_pengajuan',
        'jenis_pengalaman',
        'nama_instansi',
        'periode_mulai',
        'periode_selesai',
        'uraian_pengalaman',
        'target_sks_dimohon',
        'status',
        'total_sks_diakui',
        'catatan',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'target_sks_dimohon' => 'integer',
        'total_sks_diakui' => 'integer',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function dokumen()
    {
        return $this->hasMany(RplDokumen::class, 'rpl_pengajuan_id');
    }

    public function konversi()
    {
        return $this->hasMany(RplKonversi::class, 'rpl_pengajuan_id');
    }

    public function getApprovedSksAttribute(): int
    {
        return (int) $this->konversi()
            ->where('disetujui', true)
            ->sum('sks_diakui');
    }
}
