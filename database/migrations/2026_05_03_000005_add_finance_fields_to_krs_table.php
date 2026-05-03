<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->string('keuangan_status')->default('clear')->after('status');
            $table->text('keuangan_catatan')->nullable()->after('keuangan_status');
            $table->dateTime('keuangan_checked_at')->nullable()->after('keuangan_catatan');
        });
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropColumn([
                'keuangan_status',
                'keuangan_catatan',
                'keuangan_checked_at',
            ]);
        });
    }
};
