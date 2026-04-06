<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BukuController extends Controller
{
    public function index()
    {
        try {
            $buku = Buku::latest()->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $buku
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data buku'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $buku = Buku::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $buku
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun' => 'required|integer|min:1900|max:' . date('Y'),
            'isbn' => 'required|string|unique:bukus',
            'stok' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $buku = Buku::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil ditambahkan',
                'data' => $buku
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan buku'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $buku = Buku::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'judul' => 'sometimes|string|max:255',
                'penulis' => 'sometimes|string|max:255',
                'penerbit' => 'sometimes|string|max:255',
                'tahun' => 'sometimes|integer|min:1900|max:' . date('Y'),
                'isbn' => 'sometimes|string|unique:bukus,isbn,' . $id,
                'stok' => 'sometimes|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $buku->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil diupdate',
                'data' => $buku
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate buku'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $buku = Buku::findOrFail($id);
            
            if ($buku->peminjamans()->where('status', 'dipinjam')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku sedang dipinjam, tidak bisa dihapus'
                ], 400);
            }

            $buku->delete();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus buku'
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $keyword = $request->input('q');
            
            $buku = Buku::where('judul', 'like', "%{$keyword}%")
                        ->orWhere('penulis', 'like', "%{$keyword}%")
                        ->orWhere('isbn', 'like', "%{$keyword}%")
                        ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $buku
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari buku'
            ], 500);
        }
    }
}