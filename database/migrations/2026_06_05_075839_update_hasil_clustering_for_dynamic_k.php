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
        Schema::table('hasil_clustering', function (Blueprint $table) {
            $table->dropColumn(['jarak_ke_c1', 'jarak_ke_c2', 'jarak_ke_c3', 'jarak_ke_c4']);
            $table->json('jarak_ke_centroids')->nullable()->after('id_centroid');
        });

        Schema::table('riwayat_clustering', function (Blueprint $table) {
            $table->integer('k_jumlah')->nullable()->after('iterasi_total');
            $table->json('nama_clusters')->nullable()->after('k_jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_clustering', function (Blueprint $table) {
            $table->dropColumn('jarak_ke_centroids');
            $table->decimal('jarak_ke_c1', 10, 4)->nullable();
            $table->decimal('jarak_ke_c2', 10, 4)->nullable();
            $table->decimal('jarak_ke_c3', 10, 4)->nullable();
            $table->decimal('jarak_ke_c4', 10, 4)->nullable();
        });

        Schema::table('riwayat_clustering', function (Blueprint $table) {
            $table->dropColumn(['k_jumlah', 'nama_clusters']);
        });
    }
};
