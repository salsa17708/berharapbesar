<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Silakan login terlebih dahulu.'
            ], 401);
        }

        // Ambil user yang sedang login
        $user = Auth::user();
        
        // Ambil role user (gunakan method dari spatie/laravel-permission)
        $userRoles = $user->getRoleNames(); // Returns a collection
        
        // Cek apakah user memiliki salah satu role yang diizinkan
        $hasRole = false;
        foreach ($roles as $role) {
            if ($userRoles->contains($role)) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role Anda tidak diizinkan.',
                'required_roles' => $roles,
                'your_roles' => $userRoles->toArray()
            ], 403);
        }

        return $next($request);
    }
}