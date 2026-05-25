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
        // 1. Buat tabel riwayat_clustering
        Schema::create('riwayat_clustering', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->string('nama_riwayat');
            $table->dateTime('tanggal');
            $table->integer('jumlah_mahasiswa');
            $table->integer('iterasi_total');
            $table->text('centroid_awal'); // Simpan JSON array ID mahasiswa yang dipilih sebagai centroid
            $table->text('explained_variance_ratio'); // Simpan JSON array explained variance PCA
            $table->timestamps();
        });

        // 2. Drop tabel hasil_clustering lama jika ada (dan kosong)
        Schema::dropIfExists('hasil_clustering');

        // 3. Buat ulang tabel hasil_clustering dengan foreign key riwayat_id, dan kolom pc1, pc2
        Schema::create('hasil_clustering', function (Blueprint $table) {
            $table->id('id_hasil');
            $table->foreignId('id_riwayat')->constrained('riwayat_clustering', 'id_riwayat')->onDelete('cascade');
            $table->foreignId('id_nilai')->constrained('nilai_kuesioner', 'id_nilai')->onDelete('cascade');
            $table->foreignId('id_centroid')->constrained('centroid', 'id_centroid')->onDelete('cascade');
            $table->decimal('jarak_ke_c1', 10, 4);
            $table->decimal('jarak_ke_c2', 10, 4);
            $table->decimal('jarak_ke_c3', 10, 4);
            $table->decimal('jarak_ke_c4', 10, 4);
            $table->decimal('jarak_minimum', 10, 4);
            $table->decimal('pc1', 10, 4); // Kolom koordinat PC1 PCA
            $table->decimal('pc2', 10, 4); // Kolom koordinat PC2 PCA
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
        Schema::dropIfExists('riwayat_clustering');
    }
};
