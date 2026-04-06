<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'buku_id',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
        'denda'
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_kembali' => 'date',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke buku
    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    // Hitung denda otomatis
    public function hitungDenda()
    {
        if ($this->status === 'dikembalikan') {
            return $this->denda;
        }

        $today = now();
        if ($today->gt($this->tanggal_jatuh_tempo)) {
            $hariTerlambat = $today->diffInDays($this->tanggal_jatuh_tempo);
            return $hariTerlambat * 1000; // Denda Rp 1000/hari
        }

        return 0;
    }
}