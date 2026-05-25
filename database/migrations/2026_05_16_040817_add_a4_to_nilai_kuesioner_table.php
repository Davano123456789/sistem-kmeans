<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('nilai_kuesioner', 'a4')) {
            Schema::table('nilai_kuesioner', function (Blueprint $table) {
                $table->integer('a4')->after('a3')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('nilai_kuesioner', 'a4')) {
            Schema::table('nilai_kuesioner', function (Blueprint $table) {
                $table->dropColumn('a4');
            });
        }
    }
};
