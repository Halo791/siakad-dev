<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RplKonversi extends Model
{
    use HasFactory;

    protected $table = 'rpl_konversi';

    protected $fillable = [
        'rpl_pengajuan_id',
        'mata_kuliah_id',
        'nilai_awal',
        'nilai_konversi',
        'sks_diakui',
        'disetujui',
    ];

    protected $casts = [
        'sks_diakui' => 'integer',
        'disetujui' => 'boolean',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(RplPengajuan::class, 'rpl_pengajuan_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }
}
