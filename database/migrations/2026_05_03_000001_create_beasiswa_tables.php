<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis');
            $table->string('coverage_type')->default('partial');
            $table->unsignedTinyInteger('coverage_percent')->default(0);
            $table->unsignedInteger('kuota')->default(0);
            $table->boolean('aktif')->default(true);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('mahasiswa_beasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('beasiswa_id')->constrained('beasiswa')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->string('nomor_sk')->nullable();
            $table->string('status')->default('pengajuan');
            $table->date('mulai_berlaku')->nullable();
            $table->date('berakhir_berlaku')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'tahun_akademik_id'], 'mb_mhs_ta_idx');
            $table->index(['status', 'tahun_akademik_id'], 'mb_status_ta_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_beasiswa');
        Schema::dropIfExists('beasiswa');
    }
};
