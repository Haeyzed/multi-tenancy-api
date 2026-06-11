<?php

declare(strict_types=1);

return [

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        // Comma-separated channels sent to transaction/initialize (card is required for saved authorizations).
        'channels' => array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('PAYSTACK_CHANNELS', 'card')),
        ))),
        // Minimum charge in kobo for NGN (Paystack requires at least ₦100 for most channels).
        'min_amount' => (int) env('PAYSTACK_MIN_AMOUNT', 10_000),
    ],

    'checkout' => [
        // Paystack/Stripe redirect here first; the API verifies payment then sends the user to frontend_success_url.
        'success_url' => env('CHECKOUT_SUCCESS_URL', env('APP_URL').'/api/central/payments/paystack/callback'),
        'frontend_success_url' => env(
            'CHECKOUT_FRONTEND_SUCCESS_URL',
            rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/').'/onboard/success',
        ),
        'cancel_url' => env(
            'CHECKOUT_CANCEL_URL',
            rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/').'/onboard/cancel',
        ),
    ],

    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    // Card verification charge during trial signup (kobo). Default ₦100.
    'trial_setup_amount' => (int) env('PAYSTACK_TRIAL_SETUP_AMOUNT', 10_000),

    'trial_reminder_days' => (int) env('TRIAL_REMINDER_DAYS', 3),

];
