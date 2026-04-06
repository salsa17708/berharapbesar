<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Silakan login terlebih dahulu.'
            ], 401);
        }

        $user = Auth::user();
        $userRoles = $user->roles->pluck('name');
        
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