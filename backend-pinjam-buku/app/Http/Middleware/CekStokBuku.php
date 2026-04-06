<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Buku;

class CekStokBuku
{
    /**
     * Cek stok buku sebelum peminjaman
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Validasi input
        $request->validate([
            'buku_id' => 'required|exists:bukus,id'
        ]);
        
        // Cari buku
        $buku = Buku::find($request->buku_id);
        
        // Cek stok
        if ($buku->stok <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, stok buku sedang habis. Tidak dapat dipinjam.',
                'buku' => $buku->judul,
                'stok_tersedia' => $buku->stok
            ], 400);
        }
        
        // Cek apakah user sudah meminjam buku ini
        $user = $request->user();
        $sudahPinjam = $user->peminjamans()
                            ->where('buku_id', $buku->id)
                            ->where('status', 'dipinjam')
                            ->exists();
        
        if ($sudahPinjam) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih meminjam buku ini. Kembalikan terlebih dahulu.'
            ], 400);
        }
        
        // Simpan buku ke request untuk digunakan di controller
        $request->attributes->set('buku', $buku);
        
        return $next($request);
    }
}