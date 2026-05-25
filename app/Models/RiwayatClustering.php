<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatClustering extends Model
{
    use HasFactory;

    protected $table = 'riwayat_clustering';
    protected $primaryKey = 'id_riwayat';

    protected $fillable = [
        'nama_riwayat',
        'tanggal',
        'jumlah_mahasiswa',
        'iterasi_total',
        'centroid_awal',
        'explained_variance_ratio',
    ];

    protected $casts = [
        'centroid_awal' => 'array',
        'explained_variance_ratio' => 'array',
        'tanggal' => 'datetime',
    ];

    public function hasilClustering()
    {
        return $this->hasMany(HasilClustering::class, 'id_riwayat', 'id_riwayat');
    }
}
