<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\ChangelogType;
use App\Models\Central\PlatformChangelog;
use Illuminate\Database\Seeder;

/**
 * Seed platform changelog entries.
 */
class PlatformChangelogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlatformChangelog::query()->updateOrCreate(
            ['version' => '2.5.0'],
            [
                'title' => 'Advanced Analytics Dashboard',
                'description' => 'Introduced a new analytics dashboard with real-time metrics, custom date ranges, and export capabilities.',
                'type' => ChangelogType::Feature,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
        );

        PlatformChangelog::query()->updateOrCreate(
            ['version' => '2.4.2'],
            [
                'title' => 'Fixed API Pagination Bug',
                'description' => 'Resolved an issue where API pagination returned incorrect total counts for filtered queries.',
                'type' => ChangelogType::Fix,
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
        );

        PlatformChangelog::query()->updateOrCreate(
            ['version' => '2.4.0'],
            [
                'title' => 'Webhook Signature Verification Change',
                'description' => 'Webhook signatures now use SHA-256 instead of SHA-1. Please update your integrations accordingly.',
                'type' => ChangelogType::Breaking,
                'is_published' => true,
                'published_at' => now()->subDays(20),
            ],
        );

        PlatformChangelog::query()->updateOrCreate(
            ['version' => '2.3.5'],
            [
                'title' => 'Security Patch: XSS Prevention',
                'description' => 'Applied additional sanitization to prevent reflected XSS in search parameters.',
                'type' => ChangelogType::Security,
                'is_published' => true,
                'published_at' => now()->subDays(25),
            ],
        );

        PlatformChangelog::query()->updateOrCreate(
            ['version' => '2.3.0'],
            [
                'title' => 'Database Query Optimization',
                'description' => 'Improved query performance for tenant listing by 40% through index optimization.',
                'type' => ChangelogType::Performance,
                'is_published' => true,
                'published_at' => now()->subDays(35),
            ],
        );

        PlatformChangelog::query()->updateOrCreate(
            ['version' => '3.0.0-beta'],
            [
                'title' => 'Multi-Region Support (Beta)',
                'description' => 'Beta release of multi-region tenant deployment. Not yet ready for production.',
                'type' => ChangelogType::Feature,
                'is_published' => false,
                'published_at' => null,
            ],
        );
    }
}
