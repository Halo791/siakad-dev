<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rpl_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->string('judul_pengajuan');
            $table->string('status')->default('draft');
            $table->unsignedTinyInteger('total_sks_diakui')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'tahun_akademik_id'], 'rp_mhs_ta_idx');
            $table->index(['status'], 'rp_status_idx');
        });

        Schema::create('rpl_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpl_pengajuan_id')->constrained('rpl_pengajuan')->cascadeOnDelete();
            $table->string('jenis_dokumen');
            $table->string('file_path');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('rpl_konversi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpl_pengajuan_id')->constrained('rpl_pengajuan')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('nilai_awal')->nullable();
            $table->string('nilai_konversi')->nullable();
            $table->unsignedTinyInteger('sks_diakui')->default(0);
            $table->boolean('disetujui')->default(false);
            $table->timestamps();

            $table->index(['rpl_pengajuan_id', 'disetujui'], 'rk_pengajuan_disetujui_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpl_konversi');
        Schema::dropIfExists('rpl_dokumen');
        Schema::dropIfExists('rpl_pengajuan');
    }
};
