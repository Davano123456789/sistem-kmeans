<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Centroid extends Model
{
    use HasFactory;

    protected $table = 'centroid';
    protected $primaryKey = 'id_centroid';

    protected $fillable = [
        'kode',
        'topik',
    ];

    public function hasilClustering()
    {
        return $this->hasMany(HasilClustering::class, 'id_centroid', 'id_centroid');
    }
}
