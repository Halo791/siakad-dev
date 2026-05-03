<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_mahasiswa';

    protected $fillable = [
        'tagihan_mahasiswa_id',
        'mahasiswa_id',
        'metode_pembayaran',
        'jumlah_bayar',
        'tanggal_bayar',
        'status_verifikasi',
        'bukti_transfer',
        'diverifikasi_oleh',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'jumlah_bayar' => 'integer',
        'tanggal_bayar' => 'date',
    ];

    public function tagihan()
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'tagihan_mahasiswa_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
