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
        $domains = [
            ['domain' => 'acme-corp.saas.local', 'tenant' => 'acme-corp', 'is_primary' => true, 'is_fallback' => false, 'verified' => true],
            ['domain' => 'shop.acme.com', 'tenant' => 'acme-corp', 'is_primary' => false, 'is_fallback' => false, 'verified' => true],
            ['domain' => 'beta-solutions.saas.local', 'tenant' => 'beta-solutions', 'is_primary' => true, 'is_fallback' => false, 'verified' => true],
            ['domain' => 'gamma-innovations.saas.local', 'tenant' => 'gamma-innovations', 'is_primary' => true, 'is_fallback' => true, 'verified' => false],
            ['domain' => 'delta-works.saas.local', 'tenant' => 'delta-works', 'is_primary' => true, 'is_fallback' => false, 'verified' => true],
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
