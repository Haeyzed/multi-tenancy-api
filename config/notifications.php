<?php

declare(strict_types=1);

return [

    'email' => [
        'logo_url' => env('MAIL_LOGO_URL'),
        'brand_url' => env('MAIL_BRAND_URL', env('APP_URL')),
        'header_bg' => env('MAIL_HEADER_BG', '#1e2b2e'),
        'accent_color' => env('MAIL_ACCENT_COLOR', '#73bc1c'),
        'link_color' => env('MAIL_LINK_COLOR', '#ff641a'),
    ],

];
