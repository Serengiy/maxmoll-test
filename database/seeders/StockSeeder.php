<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 1000; $i++) {
            Stock::withoutEvents(function () {
                $stock = Stock::query()->firstOrCreate([
                    'product_id' => Product::query()->inRandomOrder()->first()->id,
                    'warehouse_id' => Warehouse::query()->inRandomOrder()->first()->id,
                    'stock' => rand(10, 1000),
                    'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
                ]);

                StockMovement::query()->create([
                    'product_id' => $stock->product_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'stock_id' => $stock->id,
                    'type' => StockMovement::CREATED_TYPE,
                    'description' => 'Initial stock',
                    'changed_at' => $stock->created_at,
                ]);
            });
        }
    }
}
