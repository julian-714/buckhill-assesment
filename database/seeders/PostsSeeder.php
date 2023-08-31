<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Post;

class PostsSeeder extends Seeder
{
    /**
     * Blog post seeder with factory
     */
    public function run(): void
    {
        Post::factory(10)->create();
    }
}
