<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * Domain Tenant Finder
 *
 * Finds tenant based on the request domain for multi-tenancy.
 */
class DomainTenantFinder extends TenantFinder
{
    /**
     * Find a tenant by the current request.
     *
     * @param Request $request
     * @return Tenant|null
     */
    public function findForRequest(Request $request): ?Tenant
    {
        $domain = $this->getDomain($request);

        return Tenant::where('domain', $domain)->first();
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
}