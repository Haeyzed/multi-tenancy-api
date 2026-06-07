<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Length
    |--------------------------------------------------------------------------
    |
    | Number of digits in the one-time password sent to the user.
    |
    */

    'length' => (int) env('OTP_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | OTP Expiry
    |--------------------------------------------------------------------------
    |
    | Minutes before an unused OTP expires.
    |
    */

    'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Resend Cooldown
    |--------------------------------------------------------------------------
    |
    | Minimum seconds between OTP resend requests for the same email and purpose.
    |
    */

    'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),

    /*
    |--------------------------------------------------------------------------
    | Maximum Verification Attempts
    |--------------------------------------------------------------------------
    |
    | Failed OTP attempts before the record is invalidated.
    |
    */

    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | Verification Token TTL
    |--------------------------------------------------------------------------
    |
    | Minutes a verification token remains valid after OTP confirmation.
    |
    */

    'verification_token_ttl_minutes' => (int) env('OTP_VERIFICATION_TOKEN_TTL_MINUTES', 15),

];
