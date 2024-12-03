<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 300; $i++) {
            Product::query()->firstOrCreate([
                'name' => fake()->word,
                'price' => fake()->randomFloat(2, 1000, 100_000),
            ]);
        }
    }
}
