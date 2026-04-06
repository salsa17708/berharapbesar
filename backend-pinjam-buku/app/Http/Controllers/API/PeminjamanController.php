<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    // Menampilkan semua peminjaman (Admin & Petugas)
    public function index(Request $request)
    {
        try {
            $peminjaman = Peminjaman::with(['user', 'buku'])
                                    ->latest()
                                    ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $peminjaman
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    // Proses peminjaman buku
    public function pinjam(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:bukus,id',
        ]);

        $buku = Buku::findOrFail($request->buku_id);
        
        // Cek stok
        if ($buku->stok <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Stok buku habis, tidak bisa dipinjam'
            ], 400);
        }

        // Cek apakah user sudah meminjam buku ini dan belum dikembalikan
        $existingPinjam = Peminjaman::where('user_id', $request->user()->id)
                                    ->where('buku_id', $request->buku_id)
                                    ->where('status', 'dipinjam')
                                    ->exists();

        if ($existingPinjam) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih meminjam buku ini'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Kurangi stok
            $buku->decrement('stok');

            // Buat peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user()->id,
                'buku_id' => $request->buku_id,
                'tanggal_pinjam' => now(),
                'tanggal_jatuh_tempo' => now()->addDays(7),
                'status' => 'dipinjam',
                'denda' => 0
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dipinjam',
                'data' => $peminjaman->load('buku')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal meminjam buku: ' . $e->getMessage()
            ], 500);
        }
    }

    // Proses pengembalian buku
    public function kembalikan($id, Request $request)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);
            
            // Validasi akses (user hanya bisa mengembalikan pinjamannya sendiri)
            if ($request->user()->hasRole('user') && $peminjaman->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }

            if ($peminjaman->status === 'dikembalikan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku sudah dikembalikan'
                ], 400);
            }

            DB::beginTransaction();
            
            // Hitung denda jika terlambat
            $today = now();
            $denda = 0;
            
            if ($today->gt($peminjaman->tanggal_jatuh_tempo)) {
                $hariTerlambat = $today->diffInDays($peminjaman->tanggal_jatuh_tempo);
                $denda = $hariTerlambat * 1000;
            }

            // Update peminjaman
            $peminjaman->update([
                'tanggal_kembali' => $today,
                'status' => 'dikembalikan',
                'denda' => $denda
            ]);

            // Tambah stok buku
            $peminjaman->buku->increment('stok');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dikembalikan' . ($denda > 0 ? " dengan denda Rp " . number_format($denda, 0, ',', '.') : ''),
                'data' => $peminjaman,
                'denda' => $denda
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengembalikan buku: ' . $e->getMessage()
            ], 500);
        }
    }

    // Riwayat peminjaman user yang login
    public function riwayatSaya(Request $request)
    {
        try {
            $peminjaman = Peminjaman::with('buku')
                                    ->where('user_id', $request->user()->id)
                                    ->latest()
                                    ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $peminjaman
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    // Dashboard stats
    public function dashboardStats(Request $request)
    {
        try {
            $stats = [
                'total_buku' => Buku::count(),
                'buku_tersedia' => Buku::where('stok', '>', 0)->count(),
                'buku_dipinjam' => Buku::where('stok', 0)->count(),
                'total_peminjaman_aktif' => Peminjaman::where('status', 'dipinjam')->count(),
                'total_peminjaman_bulan_ini' => Peminjaman::whereMonth('created_at', now()->month)->count(),
                'total_user' => \App\Models\User::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }
}