<?php

namespace Database\Seeders;

use App\Models\Product;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        foreach (range(1, 40) as $index) {
            Product::create([
                'title'       => $faker->name,
                'price'       => rand(99, 999),
                'image'       => $faker->imageUrl(),
                'description' => $faker->paragraphs(2, true),
            ]);
        }
    }
}
