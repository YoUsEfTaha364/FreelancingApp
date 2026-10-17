<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Seed realistic example jobs for local development/testing.
     *
     * - Resolves client users by role; skips gracefully if none exist.
     * - Resolves categories and skills by name — never by hard-coded ID.
     * - Uses updateOrCreate (keyed on title + user_id) for idempotency.
     * - Does not run against production data.
     */
    public function run(): void
    {
        $clients = User::where('role', UserRole::Client->value)->get();

        if ($clients->isEmpty()) {
            $this->command->warn(
                'JobSeeder: No client users found. Skipping job seeding. ' .
                'Register a client user first, then rerun: php artisan db:seed --class=JobSeeder'
            );
            return;
        }

        // Resolve categories by name
        $webDev    = Category::where('slug', 'web-development')->first();
        $mobileDev = Category::where('slug', 'mobile-development')->first();
        $uiUx      = Category::where('slug', 'ui-ux-design')->first();
        $dataAi    = Category::where('slug', 'data-science-ai')->first();
        $writing   = Category::where('slug', 'writing-translation')->first();

        // Resolve skills by name
        $php       = Skill::where('name', 'PHP')->first();
        $laravel   = Skill::where('name', 'Laravel')->first();
        $react     = Skill::where('name', 'React')->first();
        $python    = Skill::where('name', 'Python')->first();
        $figma     = Skill::where('name', 'Figma')->first();
        $writing_s = Skill::where('name', 'Content Writing')->first();
        $mysql     = Skill::where('name', 'MySQL')->first();

        $client = $clients->first();

        $jobs = [
            [
                'title'            => 'Build a Laravel REST API for E-commerce',
                'description'      => 'We need an experienced Laravel developer to build a scalable REST API for our e-commerce platform. The API must handle products, orders, payments, and user authentication with Sanctum.',
                'category'         => $webDev,
                'budget_min'       => 800,
                'budget_max'       => 2500,
                'experience_level' => 'intermediate',
                'deadline'         => now()->addMonths(2)->toDateString(),
                'skills'           => array_filter([$php?->id, $laravel?->id, $mysql?->id]),
            ],
            [
                'title'            => 'React Dashboard for Analytics Platform',
                'description'      => 'Looking for a React developer to build a modern data analytics dashboard with charts, filters, and real-time updates. Must have experience with React hooks and state management.',
                'category'         => $webDev,
                'budget_min'       => 500,
                'budget_max'       => 1800,
                'experience_level' => 'intermediate',
                'deadline'         => now()->addMonths(3)->toDateString(),
                'skills'           => array_filter([$react?->id]),
            ],
            [
                'title'            => 'Mobile App UI/UX Design — Fintech Startup',
                'description'      => 'We are a fintech startup looking for a talented UI/UX designer to create clean, intuitive designs for our mobile banking application. Deliverable: Figma design system and screens.',
                'category'         => $uiUx,
                'budget_min'       => 400,
                'budget_max'       => 1200,
                'experience_level' => 'intermediate',
                'deadline'         => now()->addMonths(1)->toDateString(),
                'skills'           => array_filter([$figma?->id]),
            ],
            [
                'title'            => 'Python Data Analysis & Visualization',
                'description'      => 'Need a data scientist to analyze our sales dataset (CSV, ~500k rows), identify trends, and produce interactive visualizations using Python. Deliver a Jupyter notebook with findings.',
                'category'         => $dataAi,
                'budget_min'       => 300,
                'budget_max'       => 900,
                'experience_level' => 'entry',
                'deadline'         => null,
                'skills'           => array_filter([$python?->id]),
            ],
            [
                'title'            => 'Technical Blog Writer — SaaS / Cloud Topics',
                'description'      => 'We need a technical writer to produce 8 long-form blog articles (1500–2500 words each) covering SaaS product features, cloud architecture, and developer tutorials.',
                'category'         => $writing,
                'budget_min'       => 200,
                'budget_max'       => 600,
                'experience_level' => 'entry',
                'deadline'         => now()->addWeeks(6)->toDateString(),
                'skills'           => array_filter([$writing_s?->id]),
            ],
        ];

        foreach ($jobs as $jobData) {
            $job = Job::updateOrCreate(
                [
                    'title'   => $jobData['title'],
                    'user_id' => $client->id,
                ],
                [
                    'category_id'      => $jobData['category']?->id,
                    'description'      => $jobData['description'],
                    'budget_min'       => $jobData['budget_min'],
                    'budget_max'       => $jobData['budget_max'],
                    'experience_level' => $jobData['experience_level'],
                    'deadline'         => $jobData['deadline'],
                    'status'           => 'open',
                ]
            );

            if (! empty($jobData['skills'])) {
                $job->skills()->sync($jobData['skills']);
            }
        }

        $this->command->info('JobSeeder: ' . count($jobs) . ' jobs seeded successfully.');
    }
}
