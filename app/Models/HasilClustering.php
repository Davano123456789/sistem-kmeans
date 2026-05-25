<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilClustering extends Model
{
    use HasFactory;

    protected $table = 'hasil_clustering';
    protected $primaryKey = 'id_hasil';

    protected $fillable = [
        'id_riwayat',
        'id_nilai',
        'id_centroid',
        'jarak_ke_c1',
        'jarak_ke_c2',
        'jarak_ke_c3',
        'jarak_ke_c4',
        'jarak_minimum',
        'pc1',
        'pc2',
        'iterasi',
    ];

    public function riwayatClustering()
    {
        return $this->belongsTo(RiwayatClustering::class, 'id_riwayat', 'id_riwayat');
    }

    public function nilaiKuesioner()
    {
        return $this->belongsTo(NilaiKuesioner::class, 'id_nilai', 'id_nilai');
    }

    public function centroid()
    {
        return $this->belongsTo(Centroid::class, 'id_centroid', 'id_centroid');
    }
}
