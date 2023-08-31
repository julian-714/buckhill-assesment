<?php

namespace Database\Seeders;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                "uuid" => Str::orderedUuid(),
                "title" => "royal canin",
                "slug" => "royal-canin",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "blue",
                "slug" => "blue",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "petsafe",
                "slug" => "petsafe",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "acana",
                "slug" => "acana",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "manapro",
                "slug" => "manapro",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "iris",
                "slug" => "iris",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "frontline",
                "slug" => "frontline",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "kitzy",
                "slug" => "kitzy",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "wag",
                "slug" => "wag",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "hills",
                "slug" => "hills",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        Brand::insert($brands);
    }
}
