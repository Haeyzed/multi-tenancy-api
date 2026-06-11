<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Tenancy\TenantDomain;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\ServerVariable;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ScrambleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureCentralApiDocs();
        $this->configureTenantApiDocs();
    }

    private function configureCentralApiDocs(): void
    {
        Scramble::configure()
            ->useConfig(array_merge(config('scramble', []), [
                'api_path' => 'api/central',
                'ui' => array_merge(config('scramble.ui', []), [
                    'title' => config('app.name').' — Central API',
                ]),
                'servers' => [
                    'Local (central)' => '/api/central',
                ],
            ]))
            ->routes(fn (Route $route): bool => str_starts_with($route->uri, 'api/central'))
            ->expose(
                ui: fn (Router $router, mixed $action) => $router
                    ->get('docs/central', $action)
                    ->name('scramble.docs.ui'),
                document: fn (Router $router, mixed $action) => $router
                    ->get('docs/central/api.json', $action)
                    ->name('scramble.docs.document'),
            )
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->info->title = config('app.name').' Central API';
            });
    }

    private function configureTenantApiDocs(): void
    {
        $tenantDomain = env(
            'SCRAMBLE_TENANT_DOMAIN',
            'acme-corp.'.TenantDomain::suffix(),
        );
        $scheme = $this->tenantApiScheme();

        Scramble::registerApi('tenant', [
            'api_path' => 'api',
            'info' => [
                'version' => config('scramble.info.version', '0.0.1'),
                'description' => 'Tenant store API. Send requests to the tenant host (see Server variables). '
                    ."Use **{$scheme}** locally (Herd `.test` is usually HTTP unless you enabled SSL). "
                    .'The `tenant_domain` value must match a row in the `domains` table '
                    .'(e.g. acme-corp.'.TenantDomain::suffix().').',
            ],
            'ui' => [
                'title' => config('app.name').' — Tenant API',
                'hide_try_it' => config('scramble.ui.hide_try_it', false),
                'theme' => config('scramble.ui.theme', 'light'),
                'layout' => config('scramble.ui.layout', 'responsive'),
            ],
            'servers' => [
                'Tenant host' => "{$scheme}://{tenant_domain}/api",
            ],
            'middleware' => config('scramble.middleware', ['web', RestrictedDocsAccess::class]),
            'security_strategy' => [
                \Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy::class,
                [
                    'middleware' => ['auth:sanctum', 'auth', 'auth:*'],
                    'scheme' => SecurityScheme::http('bearer', 'Sanctum')
                        ->as('sanctum')
                        ->setDescription('Sanctum API token returned by POST /api/auth/login on the tenant domain.'),
                ],
            ],
        ])
            ->routes(function (Route $route): bool {
                if (str_starts_with($route->uri, 'api/central')) {
                    return false;
                }

                return str_starts_with($route->getName() ?? '', 'tenant.')
                    || (Str::startsWith($route->uri, 'api') && str_contains($route->getActionName(), 'Tenant\\'));
            })
            ->expose(
                ui: fn (Router $router, mixed $action) => $router
                    ->get('docs/tenant', $action)
                    ->name('scramble.tenant.docs.ui'),
                document: fn (Router $router, mixed $action) => $router
                    ->get('docs/tenant/api.json', $action)
                    ->name('scramble.tenant.docs.document'),
            )
            ->withServerVariables([
                'tenant_domain' => ServerVariable::make(
                    default: $tenantDomain,
                    description: 'Full tenant host from `domains.domain` (e.g. acme-corp.'.TenantDomain::suffix().')',
                ),
            ])
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->info->title = config('app.name').' Tenant API';
            });
    }

    /**
     * Local Herd serves .test sites over HTTP unless you explicitly secured the site.
     */
    private function tenantApiScheme(): string
    {
        if ($scheme = env('SCRAMBLE_TENANT_SCHEME')) {
            return rtrim($scheme, '://');
        }

        return str_starts_with((string) config('app.url'), 'https://') ? 'https' : 'http';
    }
}
