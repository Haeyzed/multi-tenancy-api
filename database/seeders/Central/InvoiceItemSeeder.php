<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\InvoiceItem;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed line items for demo invoices.
 */
class InvoiceItemSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'invoice_number' => 'INV-2026-0001',
                'description' => 'Starter Plan - Monthly',
                'quantity' => 1,
                'unit_amount' => 2_500_000,
                'amount' => 2_500_000,
                'plan' => 'starter',
                'period_start' => now()->addDays(15),
                'period_end' => now()->addDays(45),
            ],
            [
                'invoice_number' => 'INV-2026-0002',
                'description' => 'Starter Plan - Monthly',
                'quantity' => 1,
                'unit_amount' => 2_500_000,
                'amount' => 2_500_000,
                'plan' => 'starter',
                'period_start' => now()->subDays(15),
                'period_end' => now()->addDays(15),
            ],
            [
                'invoice_number' => 'INV-2026-0003',
                'description' => 'Professional Plan - Yearly',
                'quantity' => 1,
                'unit_amount' => 75_000_000,
                'amount' => 75_000_000,
                'plan' => 'professional',
                'period_start' => now()->subDays(80),
                'period_end' => now()->addDays(285),
            ],
            [
                'invoice_number' => 'INV-2026-0003',
                'description' => 'Early Bird Discount',
                'quantity' => 1,
                'unit_amount' => -7_500_000,
                'amount' => -7_500_000,
                'plan' => null,
                'period_start' => null,
                'period_end' => null,
            ],
            [
                'invoice_number' => 'INV-2026-0004',
                'description' => 'Enterprise Plan - Monthly',
                'quantity' => 1,
                'unit_amount' => 20_000_000,
                'amount' => 20_000_000,
                'plan' => 'enterprise',
                'period_start' => now()->subDays(35),
                'period_end' => now()->subDays(5),
            ],
            [
                'invoice_number' => 'INV-2026-0005',
                'description' => 'Starter Plan - Monthly',
                'quantity' => 1,
                'unit_amount' => 2_500_000,
                'amount' => 2_500_000,
                'plan' => 'starter',
                'period_start' => now()->subDays(90),
                'period_end' => now()->subDays(60),
            ],
        ];

        foreach ($items as $item) {
            InvoiceItem::query()->updateOrCreate(
                [
                    'invoice_id' => $this->invoice($item['invoice_number'])->id,
                    'description' => $item['description'],
                ],
                [
                    'quantity' => $item['quantity'],
                    'unit_amount' => $item['unit_amount'],
                    'amount' => $item['amount'],
                    'plan_id' => $item['plan'] ? $this->plan($item['plan'])->id : null,
                    'period_start' => $item['period_start'],
                    'period_end' => $item['period_end'],
                ],
            );
        }
    }
}
