<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpl_pengajuan', function (Blueprint $table) {
            $table->string('jenis_pengalaman')->nullable()->after('judul_pengajuan');
            $table->string('nama_instansi')->nullable()->after('jenis_pengalaman');
            $table->date('periode_mulai')->nullable()->after('nama_instansi');
            $table->date('periode_selesai')->nullable()->after('periode_mulai');
            $table->text('uraian_pengalaman')->nullable()->after('periode_selesai');
            $table->unsignedTinyInteger('target_sks_dimohon')->default(0)->after('uraian_pengalaman');
        });
    }

    public function down(): void
    {
        Schema::table('rpl_pengajuan', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pengalaman',
                'nama_instansi',
                'periode_mulai',
                'periode_selesai',
                'uraian_pengalaman',
                'target_sks_dimohon',
            ]);
        });
    }
};
