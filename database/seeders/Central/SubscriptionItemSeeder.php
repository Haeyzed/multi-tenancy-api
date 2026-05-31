<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\SubscriptionItem;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed subscription line items.
 */
class SubscriptionItemSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['tenant' => 'acme-corp', 'plan' => 'starter', 'quantity' => 1, 'unit_price' => 2_500_000, 'total_price' => 2_500_000],
            ['tenant' => 'beta-solutions', 'plan' => 'professional', 'quantity' => 1, 'unit_price' => 75_000_000, 'total_price' => 67_500_000],
            ['tenant' => 'gamma-innovations', 'plan' => 'starter', 'quantity' => 1, 'unit_price' => 0, 'total_price' => 0],
            ['tenant' => 'delta-works', 'plan' => 'enterprise', 'quantity' => 1, 'unit_price' => 20_000_000, 'total_price' => 20_000_000],
            ['tenant' => 'epsilon-ltd', 'plan' => 'starter', 'quantity' => 1, 'unit_price' => 2_500_000, 'total_price' => 2_500_000],
        ];

        foreach ($items as $item) {
            $plan = $this->plan($item['plan']);

            SubscriptionItem::query()->updateOrCreate(
                [
                    'subscription_id' => $this->subscription($item['tenant'])->id,
                    'plan_id' => $plan->id,
                ],
                [
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ],
            );
        }
    }
}
