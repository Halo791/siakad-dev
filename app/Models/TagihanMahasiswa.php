<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'tagihan_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'jenis_tagihan',
        'nominal',
        'terbayar',
        'status',
        'jatuh_tempo',
        'catatan',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'terbayar' => 'integer',
        'jatuh_tempo' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranMahasiswa::class, 'tagihan_mahasiswa_id');
    }
}
