<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\Domain;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed custom domains for demo tenants.
 */
class DomainSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $base = config('tenancy.tenant_domain_base', 'multi-tenancy-api.test');

        $domains = [
            ['domain' => "acme-corp.{$base}", 'tenant' => 'acme-corp', 'is_primary' => true, 'is_fallback' => false, 'verified' => true],
            ['domain' => 'shop.acme.com', 'tenant' => 'acme-corp', 'is_primary' => false, 'is_fallback' => false, 'verified' => true],
            ['domain' => "beta-solutions.{$base}", 'tenant' => 'beta-solutions', 'is_primary' => true, 'is_fallback' => false, 'verified' => true],
            ['domain' => "gamma-innovations.{$base}", 'tenant' => 'gamma-innovations', 'is_primary' => true, 'is_fallback' => true, 'verified' => false],
            ['domain' => "delta-works.{$base}", 'tenant' => 'delta-works', 'is_primary' => true, 'is_fallback' => false, 'verified' => true],
        ];

        foreach ($domains as $domain) {
            Domain::query()->updateOrCreate(
                ['domain' => $domain['domain']],
                [
                    'tenant_id' => $this->tenant($domain['tenant'])->id,
                    'is_primary' => $domain['is_primary'],
                    'is_fallback' => $domain['is_fallback'],
                    'verified' => $domain['verified'],
                ],
            );
        }
    }
}
