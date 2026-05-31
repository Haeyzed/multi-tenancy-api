<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\AnnouncementTargetAudience;
use App\Enums\Central\AnnouncementType;
use App\Models\Central\PlatformAnnouncement;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed platform announcements for demo data.
 */
class PlatformAnnouncementSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlatformAnnouncement::query()->updateOrCreate(
            ['title' => 'Scheduled Maintenance - June 15'],
            [
                'body' => 'We will be performing scheduled maintenance on June 15, 2026 from 02:00 to 04:00 UTC. Some services may be temporarily unavailable.',
                'type' => AnnouncementType::Maintenance,
                'target_audience' => AnnouncementTargetAudience::All,
                'target_plans' => null,
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(15),
            ],
        );

        PlatformAnnouncement::query()->updateOrCreate(
            ['title' => 'New Feature: Advanced Analytics Dashboard'],
            [
                'body' => 'We are excited to announce the new Analytics Dashboard available for Professional and Enterprise plans. Gain deeper insights into your business metrics.',
                'type' => AnnouncementType::Feature,
                'target_audience' => AnnouncementTargetAudience::PlanSpecific,
                'target_plans' => [
                    $this->plan('professional')->id,
                    $this->plan('enterprise')->id,
                ],
                'is_active' => true,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(30),
            ],
        );

        PlatformAnnouncement::query()->updateOrCreate(
            ['title' => 'Security Alert: Password Reset Recommended'],
            [
                'body' => 'As a precautionary measure, we recommend all users reset their passwords. No breach has occurred, but this is part of our regular security posture.',
                'type' => AnnouncementType::Alert,
                'target_audience' => AnnouncementTargetAudience::All,
                'target_plans' => null,
                'is_active' => false,
                'starts_at' => now()->subDays(30),
                'ends_at' => now()->subDays(20),
            ],
        );

        PlatformAnnouncement::query()->updateOrCreate(
            ['title' => 'Platform Update v2.5 Released'],
            [
                'body' => 'General information about the latest platform improvements and bug fixes.',
                'type' => AnnouncementType::Info,
                'target_audience' => AnnouncementTargetAudience::All,
                'target_plans' => null,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => null,
            ],
        );
    }
}
