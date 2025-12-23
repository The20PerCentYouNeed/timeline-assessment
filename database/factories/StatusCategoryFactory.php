<?php

namespace Database\Factories;

use App\Models\StatusCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusCategory>
 */
class StatusCategoryFactory extends Factory
{
    protected $model = StatusCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Pending', 'Complete', 'Reject']),
        ];
    }
}
