<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed a practical set of freelancing categories.
     * Uses updateOrCreate for idempotency — safe to rerun.
     */
    public function run(): void
    {
        $categories = [
            'Web Development',
            'Mobile Development',
            'UI/UX Design',
            'Graphic Design',
            'Writing & Translation',
            'Digital Marketing',
            'Data Science & AI',
            'DevOps & Cloud',
            'QA & Testing',
            'Virtual Assistant',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
