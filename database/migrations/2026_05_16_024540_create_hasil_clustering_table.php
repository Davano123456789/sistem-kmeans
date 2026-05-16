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
        Schema::create('hasil_clustering', function (Blueprint $table) {
            $table->id('id_hasil');
            $table->foreignId('id_nilai')->constrained('nilai_kuesioner', 'id_nilai')->onDelete('cascade');
            $table->foreignId('id_centroid')->constrained('centroid', 'id_centroid')->onDelete('cascade');
            $table->decimal('jarak_ke_c1', 10, 4);
            $table->decimal('jarak_ke_c2', 10, 4);
            $table->decimal('jarak_ke_c3', 10, 4);
            $table->decimal('jarak_ke_c4', 10, 4);
            $table->decimal('jarak_minimum', 10, 4);
            $table->integer('iterasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_clustering');
    }
};
