<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraph(5),
            'uuid' => Str::orderedUuid(),
            'metadata' => [
                'image' => Str::orderedUuid(),
                'author' => fake()->name()
            ]
        ];
    }
}
