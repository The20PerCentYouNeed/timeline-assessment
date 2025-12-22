<?php

namespace Database\Seeders;

use App\Models\StepCategory;
use Illuminate\Database\Seeder;

class StepCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            '1st Interview',
            'Tech Assessment',
            'Other',
        ];

        foreach ($categories as $category) {
            StepCategory::create([
                'title' => json_encode(['en' => $category]),
            ]);
        }
    }
}
