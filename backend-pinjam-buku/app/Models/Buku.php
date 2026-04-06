<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'penulis',
        'penerbit',
        'tahun',
        'isbn',
        'stok',
        'deskripsi',
        'gambar'
    ];

    // Relasi ke peminjaman
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }

    // Cek ketersediaan
    public function isTersedia()
    {
        return $this->stok > 0;
    }
}