<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();
        
        // ✅ SIMPLE & CLEAN
        if (!$user || !$user->can($permission)) {
            $permissions = method_exists($user, 'getAllPermissions')
                ? $user->getAllPermissions()->pluck('name')->toArray()
                : [];

            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'message' => "Permission required: {$permission}",
                    'your_permissions' => $permissions,
                ], 403)
                : redirect()->back()->with('error', "No permission: {$permission}");
        }

        return $next($request);
    }
}