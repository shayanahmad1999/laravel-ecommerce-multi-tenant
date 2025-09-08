<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Role Middleware
 *
 * Middleware to check if authenticated user has required role or user type.
 * Supports both Spatie Laravel Permission roles and custom user_type field.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            Log::warning('Unauthenticated access attempt to protected route', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check user type (for backward compatibility)
        if ($role === 'admin' && !$user->isAdmin()) {
            Log::warning('Access denied: User does not have admin privileges', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_role' => $role,
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. Admin privileges required.'], 403);
            }

            abort(403, 'Access denied. Admin privileges required.');
        }

        if ($role === 'customer' && !$user->isCustomer()) {
            Log::warning('Access denied: User does not have customer privileges', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_role' => $role,
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. Customer access only.'], 403);
            }

            abort(403, 'Access denied. Customer access only.');
        }

        // Check Spatie Laravel Permission roles
        if ($role !== 'admin' && $role !== 'customer' && !$user->hasRole($role)) {
            Log::warning('Access denied: User does not have required role', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_role' => $role,
                'user_roles' => $user->getRoleNames()->toArray(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => "Access denied. {$role} role required."], 403);
            }

            abort(403, "Access denied. {$role} role required.");
        }

        return $next($request);
    }
}
