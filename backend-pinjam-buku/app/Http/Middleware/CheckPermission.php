<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Silakan login terlebih dahulu.'
            ], 401);
        }

        $user = Auth::user();
        
        // Admin memiliki semua akses (opsional, bisa dihapus jika tidak ingin)
        if ($user->hasRole('admin')) {
            return $next($request);
        }
        
        // Cek permission menggunakan spatie/laravel-permission
        if (!$user->can($permission)) {
            return response()->json([
                'success' => false,
                'message' => "Akses ditolak. Anda tidak memiliki permission: {$permission}",
                'required_permission' => $permission,
                'your_permissions' => $user->getAllPermissions()->pluck('name')
            ], 403);
        }

        return $next($request);
    }
}