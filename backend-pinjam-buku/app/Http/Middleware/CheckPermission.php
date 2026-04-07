<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Silakan login terlebih dahulu.'
            ], 401);
        }
         /** @var \App\Models\User $user */ 
        $user = Auth::user();
        
        // Admin memiliki semua akses
        if ($user->hasRole('admin')) {
            return $next($request);
        }
        
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