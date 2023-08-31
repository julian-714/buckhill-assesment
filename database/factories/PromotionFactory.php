<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $validFrom = date('Y-m-d');
        $validTo = date('Y-m-d', strtotime("+3 months", strtotime($validFrom)));

        return [
            'uuid' => Str::orderedUuid(),
            'title' => fake()->sentence(5),
            'content' => fake()->paragraph(5),
            'metadata' => [
                'image' => Str::orderedUuid(),
                'valid_to' => date('Y-m-d', strtotime($validTo)),
                'valid_from' => date('Y-m-d', strtotime($validFrom))
            ]
        ];
    }
}
