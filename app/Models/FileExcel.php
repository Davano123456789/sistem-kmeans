<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileExcel extends Model
{
    use HasFactory;

    protected $table = 'file_excel';
    protected $primaryKey = 'id_file';

    protected $fillable = [
        'nama',
        'tanggal_upload',
        'id_pengguna',
        'laporan_cleaning',
    ];

    protected $casts = [
        'laporan_cleaning' => 'array',
    ];

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id_pengguna');
    }
}
