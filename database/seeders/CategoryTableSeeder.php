<?php

namespace Database\Seeders;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                "uuid" => Str::orderedUuid(),
                "title" => "pet clean-up and odor control",
                "slug" => "pet-clean-up-and-odor-control",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "cat litter",
                "slug" => "cat-litter",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "wet pet food",
                "slug" => "wet-pet-food",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "pet oral care",
                "slug" => "pet-oral-care",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "heartworm medication",
                "slug" => "heartworm-medication",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "pet vitamins and supplements",
                "slug" => "pet-vitamins-and-supplements",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "pet grooming supplies",
                "slug" => "pet-grooming-supplies",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "flea and tick medication",
                "slug" => "flea-and-tick-medication",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "pet treats and chews",
                "slug" => "pet-treats-and-chews",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "dry dog food",
                "slug" => "dry-dog-food",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        Category::insert($categories);
    }
}
