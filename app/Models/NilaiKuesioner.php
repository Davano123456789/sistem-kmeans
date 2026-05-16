<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiKuesioner extends Model
{
    use HasFactory;

    protected $table = 'nilai_kuesioner';
    protected $primaryKey = 'id_nilai';

    protected $fillable = [
        'id_mahasiswa',
        'id_file',
        'a1',
        'a2',
        'a3',
        'a4',
        'b1',
        'b2',
        'b3',
        'b4',
        'd1',
        'd2',
        'd3',
        'd4',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function fileExcel()
    {
        return $this->belongsTo(FileExcel::class, 'id_file', 'id_file');
    }
}
