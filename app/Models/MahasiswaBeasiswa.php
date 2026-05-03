<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBeasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa_beasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'beasiswa_id',
        'tahun_akademik_id',
        'nomor_sk',
        'status',
        'mulai_berlaku',
        'berakhir_berlaku',
        'catatan',
        'disetujui_oleh',
    ];

    protected $casts = [
        'mulai_berlaku' => 'date',
        'berakhir_berlaku' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function beasiswa()
    {
        return $this->belongsTo(Beasiswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
