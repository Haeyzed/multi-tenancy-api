<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Services\Central\TenantOwnerProvisioningService;
use Illuminate\Console\Command;

class ProvisionTenantOwnersCommand extends Command
{
    protected $signature = 'tenants:provision-owners
                            {tenant? : Tenant UUID or slug}
                            {--missing : Only provision tenants without owner_user_id in meta}';

    protected $description = 'Provision tenant owner accounts inside tenant databases';

    public function handle(TenantOwnerProvisioningService $provisioning): int
    {
        $query = Tenant::query();

        if ($this->argument('tenant')) {
            $identifier = (string)$this->argument('tenant');
            $query->where('id', $identifier)->orWhere('slug', $identifier);
        } elseif ($this->option('missing')) {
            $query->where(function ($builder) {
                $builder->whereNull('meta')
                    ->orWhereNull('meta->owner_user_id');
            });
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants matched the criteria.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $database = $tenant->database()->getName();

            if (! $tenant->database()->manager()->databaseExists($database)) {
                $this->warn("Skipping {$tenant->name} ({$tenant->id}): database does not exist.");

                continue;
            }

            try {
                $result = $provisioning->provision($tenant);
            } catch (\Throwable $exception) {
                $this->error("Failed to provision {$tenant->name} ({$tenant->id}): {$exception->getMessage()}");

                continue;
            }

            if ($result->created) {
                $this->info("Provisioned owner for {$tenant->name} ({$tenant->id}).");
            } else {
                $this->line("Owner already exists for {$tenant->name} ({$tenant->id}).");
            }
        }

        return self::SUCCESS;
    }
}
