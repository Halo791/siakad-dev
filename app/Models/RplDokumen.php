<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RplDokumen extends Model
{
    use HasFactory;

    protected $table = 'rpl_dokumen';

    protected $fillable = [
        'rpl_pengajuan_id',
        'jenis_dokumen',
        'file_path',
        'keterangan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(RplPengajuan::class, 'rpl_pengajuan_id');
    }
}
