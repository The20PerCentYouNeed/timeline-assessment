<?php

namespace Database\Factories;

use App\Models\StepCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StepCategory>
 */
class StepCategoryFactory extends Factory
{
    protected $model = StepCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => json_encode(['en' => fake()->randomElement(['1st Interview', 'Tech Assessment', 'Offer', 'Other'])]),
        ];
    }
}
