<?php

namespace Database\Seeders;

use App\Models\StatusCategory;
use Illuminate\Database\Seeder;

class StatusCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Pending',
            'Complete',
            'Reject',
        ];

        foreach ($categories as $category) {
            StatusCategory::create([
                'title' => $category,
            ]);
        }
    }
}
