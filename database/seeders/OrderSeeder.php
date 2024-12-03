<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 1000; $i++) {
            $status = fake()->randomElement([
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]);

            $order = Order::query()->firstOrCreate([
                'customer' => fake()->firstName,
                'completed_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
                'warehouse_id' => fake()->numberBetween(1, 10),
                'created_at' => fake()->dateTimeBetween('-1 year', '-1 week'),
                'status' => $status
            ]);

            $count = fake()->numberBetween(1, 10);
            $order->product()->attach(
                Product::query()->inRandomOrder()->first()->id,
                ['count' => $status === Order::STATUS_CANCELLED ? 0 - $count : $count]
            );
        }

        for ($i = 1; $i <= rand(50, 70); $i++) {
            $status = fake()->randomElement([
                Order::STATUS_ACTIVE,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]);
            $order = Order::query()->firstOrCreate([
                'customer' => fake()->firstName,
                'completed_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
                'warehouse_id' => fake()->numberBetween(1, 10),
                'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
                'status' => $status,
            ]);

            $count = fake()->numberBetween(1, 10);
            $order->product()->attach(
                Product::query()->inRandomOrder()->first()->id,
                ['count' => $status === Order::STATUS_CANCELLED ? 0 - $count : $count]
            );
        }
    }
}
