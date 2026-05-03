<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->string('jenis_tagihan');
            $table->unsignedBigInteger('nominal')->default(0);
            $table->unsignedBigInteger('terbayar')->default(0);
            $table->string('status')->default('belum_bayar');
            $table->date('jatuh_tempo')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'tahun_akademik_id'], 'tm_mhs_ta_idx');
            $table->index(['status', 'jenis_tagihan'], 'tm_status_jenis_idx');
        });

        Schema::create('pembayaran_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_mahasiswa_id')->constrained('tagihan_mahasiswa')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('metode_pembayaran');
            $table->unsignedBigInteger('jumlah_bayar')->default(0);
            $table->date('tanggal_bayar')->nullable();
            $table->string('status_verifikasi')->default('pending');
            $table->string('bukti_transfer')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'status_verifikasi'], 'pm_mhs_status_idx');
            $table->index(['tagihan_mahasiswa_id', 'status_verifikasi'], 'pm_tagihan_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_mahasiswa');
        Schema::dropIfExists('tagihan_mahasiswa');
    }
};
