<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beasiswa extends Model
{
    use HasFactory;

    protected $table = 'beasiswa';

    protected $fillable = [
        'nama',
        'jenis',
        'coverage_type',
        'coverage_percent',
        'kuota',
        'aktif',
        'deskripsi',
    ];

    protected $casts = [
        'coverage_percent' => 'integer',
        'kuota' => 'integer',
        'aktif' => 'boolean',
    ];

    public function mahasiswaBeasiswa()
    {
        return $this->hasMany(MahasiswaBeasiswa::class);
    }
}
