<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blokir_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->nullOnDelete();
            $table->string('tipe_blokir');
            $table->string('status')->default('aktif');
            $table->text('alasan');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dicabut_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'tipe_blokir', 'status'], 'bm_mhs_tipe_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blokir_mahasiswa');
    }
};
