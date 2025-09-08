<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tenant Middleware
 *
 * Middleware to identify and set the current tenant based on the request domain.
 * Handles multi-tenancy by switching database connections and setting tenant context.
 */
class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $domain = $this->getDomain($request);

        // Try to get tenant from cache first
        $cacheKey = "tenant:{$domain}";
        $tenant = Cache::remember($cacheKey, 3600, function () use ($domain) {
            return Tenant::where('domain', $domain)->first();
        });

        if (!$tenant) {
            // Handle tenant not found
            return $this->handleTenantNotFound($request, $domain, $next);
        }

        if (!$tenant->is_active) {
            Log::warning('Access attempt to inactive tenant', [
                'domain' => $domain,
                'tenant_id' => $tenant->id,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tenant is currently inactive.'], 403);
            }

            abort(403, 'This tenant is currently inactive. Please contact support.');
        }

        try {
            // Set current tenant
            $tenant->makeCurrent();

            // Add tenant info to request for later use
            $request->merge(['current_tenant' => $tenant]);

            Log::info('Tenant context set', [
                'tenant_id' => $tenant->id,
                'domain' => $domain,
                'user_id' => Auth::id(),
            ]);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error setting tenant context', [
                'tenant_id' => $tenant->id,
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unable to process request. Please try again.'], 500);
            }

            abort(500, 'Service temporarily unavailable. Please try again later.');
        }
    }

    /**
     * Get the domain from the request.
     *
     * @param Request $request
     * @return string
     */
    private function getDomain(Request $request): string
    {
        $domain = $request->getHost();

        // Handle local development environments
        if ($this->isDevelopmentDomain($domain)) {
            return 'localhost';
        }

        // Remove www prefix for consistency
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        return $domain;
    }

    /**
     * Check if the domain is a development domain.
     *
     * @param string $domain
     * @return bool
     */
    private function isDevelopmentDomain(string $domain): bool
    {
        return in_array($domain, ['localhost', '127.0.0.1', '::1']) ||
               str_contains($domain, '.test') ||
               str_contains($domain, '.local');
    }

    /**
     * Handle case when tenant is not found.
     *
     * @param Request $request
     * @param string $domain
     * @param Closure $next
     * @return mixed
     */
    private function handleTenantNotFound(Request $request, string $domain, Closure $next): mixed
    {
        Log::warning('Tenant not found for domain', [
            'domain' => $domain,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // In development, create a default tenant
        if (app()->environment(['local', 'development']) && $this->isDevelopmentDomain($domain)) {
            try {
                $tenant = Tenant::create([
                    'name' => 'Development Tenant',
                    'domain' => $domain,
                    'database' => config('database.connections.mysql.database'),
                    'is_active' => true,
                ]);

                Log::info('Development tenant created', [
                    'tenant_id' => $tenant->id,
                    'domain' => $domain,
                ]);

                $tenant->makeCurrent();
                $request->merge(['current_tenant' => $tenant]);

                return $next($request);
            } catch (\Exception $e) {
                Log::error('Failed to create development tenant', [
                    'domain' => $domain,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        abort(404, 'The requested domain is not configured in our system.');
    }
}
