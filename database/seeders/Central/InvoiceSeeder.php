<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\InvoiceStatus;
use App\Models\Central\Invoice;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed demo invoices and link subscriptions.latest_invoice_id afterward.
 */
class InvoiceSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = [
            [
                'tenant' => 'acme-corp',
                'invoice_number' => 'INV-2026-0001',
                'status' => InvoiceStatus::Draft,
                'amount_due' => 2_500_000,
                'amount_paid' => 0,
                'amount_remaining' => 2_500_000,
                'billing_period_start' => now()->addDays(15),
                'billing_period_end' => now()->addDays(45),
                'due_date' => now()->addDays(45),
                'paid_at' => null,
                'pdf_url' => null,
                'payment_intent_id' => null,
                'line_items' => [
                    ['description' => 'Starter Plan - Monthly', 'amount' => 2_500_000],
                ],
                'notes' => null,
            ],
            [
                'tenant' => 'acme-corp',
                'invoice_number' => 'INV-2026-0002',
                'status' => InvoiceStatus::Paid,
                'amount_due' => 2_500_000,
                'amount_paid' => 2_500_000,
                'amount_remaining' => 0,
                'billing_period_start' => now()->subDays(15),
                'billing_period_end' => now()->addDays(15),
                'due_date' => now()->addDays(15),
                'paid_at' => now()->subDays(14),
                'pdf_url' => 'https://cdn.platform.com/invoices/INV-2026-0002.pdf',
                'payment_intent_id' => 'pi_stripe_12345',
                'line_items' => [
                    ['description' => 'Starter Plan - Monthly', 'amount' => 2_500_000],
                ],
                'notes' => 'Thank you for your business!',
            ],
            [
                'tenant' => 'beta-solutions',
                'invoice_number' => 'INV-2026-0003',
                'status' => InvoiceStatus::Open,
                'amount_due' => 67_500_000,
                'amount_paid' => 0,
                'amount_remaining' => 67_500_000,
                'billing_period_start' => now()->subDays(80),
                'billing_period_end' => now()->addDays(285),
                'due_date' => now()->addDays(7),
                'paid_at' => null,
                'pdf_url' => 'https://cdn.platform.com/invoices/INV-2026-0003.pdf',
                'payment_intent_id' => 'pi_stripe_54321',
                'line_items' => [
                    ['description' => 'Professional Plan - Yearly', 'amount' => 75_000_000],
                    ['description' => 'Discount (10%)', 'amount' => -7_500_000],
                ],
                'notes' => null,
            ],
            [
                'tenant' => 'delta-works',
                'invoice_number' => 'INV-2026-0004',
                'status' => InvoiceStatus::Open,
                'amount_due' => 20_000_000,
                'amount_paid' => 0,
                'amount_remaining' => 20_000_000,
                'billing_period_start' => now()->subDays(35),
                'billing_period_end' => now()->subDays(5),
                'due_date' => now()->subDays(5),
                'paid_at' => null,
                'pdf_url' => null,
                'payment_intent_id' => null,
                'line_items' => [
                    ['description' => 'Enterprise Plan - Monthly', 'amount' => 20_000_000],
                ],
                'notes' => 'Payment failed - card expired',
            ],
            [
                'tenant' => 'epsilon-ltd',
                'invoice_number' => 'INV-2026-0005',
                'status' => InvoiceStatus::Void,
                'amount_due' => 2_500_000,
                'amount_paid' => 0,
                'amount_remaining' => 0,
                'billing_period_start' => now()->subDays(90),
                'billing_period_end' => now()->subDays(60),
                'due_date' => now()->subDays(60),
                'paid_at' => null,
                'pdf_url' => null,
                'payment_intent_id' => null,
                'line_items' => [
                    ['description' => 'Starter Plan - Monthly', 'amount' => 2_500_000],
                ],
                'notes' => 'Voided due to account cancellation',
            ],
        ];

        foreach ($invoices as $invoice) {
            $tenant = $this->tenant($invoice['tenant']);

            Invoice::query()->updateOrCreate(
                ['invoice_number' => $invoice['invoice_number']],
                [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $this->subscription($invoice['tenant'])->id,
                    'status' => $invoice['status'],
                    'amount_due' => $invoice['amount_due'],
                    'amount_paid' => $invoice['amount_paid'],
                    'amount_remaining' => $invoice['amount_remaining'],
                    'currency' => 'NGN',
                    'billing_period_start' => $invoice['billing_period_start'],
                    'billing_period_end' => $invoice['billing_period_end'],
                    'due_date' => $invoice['due_date'],
                    'paid_at' => $invoice['paid_at'],
                    'pdf_url' => $invoice['pdf_url'],
                    'payment_intent_id' => $invoice['payment_intent_id'],
                    'line_items' => $invoice['line_items'],
                    'notes' => $invoice['notes'],
                ],
            );
        }

        $latestInvoices = [
            'acme-corp' => 'INV-2026-0002',
            'beta-solutions' => 'INV-2026-0003',
            'delta-works' => 'INV-2026-0004',
            'epsilon-ltd' => 'INV-2026-0005',
        ];

        foreach ($latestInvoices as $tenantSlug => $invoiceNumber) {
            $this->subscription($tenantSlug)->update([
                'latest_invoice_id' => $this->invoice($invoiceNumber)->id,
            ]);
        }
    }
}
