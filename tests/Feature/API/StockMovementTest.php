<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_stock_movement_can_be_retrieved_and_filtered(): void
    {
        $filter = [
          'paginate' => rand(5, 10),
          'page' => rand(1, 5),
          'warehouse_id' => Warehouse::query()->inRandomOrder()->first()->id,
          'product_id' => Product::query()->inRandomOrder()->first()->id,
          'from' => now()->subDays(rand(1, 100))->format('Y-m-d H:i:s'),
          'to' => now()->subDays(rand(1, 3))->format('Y-m-d H:i:s'),
          'day' => now(),
          'type' => fake()->randomElement([
              StockMovement::CREATED_TYPE,
              StockMovement::UPDATED_TYPE,
              StockMovement::DELETED_TYPE,
          ]),
        ];
        $response = $this->getJson('/api/stock-movements' . '?' . http_build_query($filter));

        $response->assertStatus(200);
    }
}
