<?php

namespace Tests\Feature\API;

use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_orders_can_be_retrieved(): void
    {
        $filters = [
            'paginate' => rand(5, 10),
            'page' => rand(1, 5),
//            'customer' => Order::query()->inRandomOrder()->first()->customer,
            'warehouse_id' => Warehouse::query()->inRandomOrder()->first()->id,
            'include' => 'product,warehouse',
            'status' => fake()->randomElement([
                Order::STATUS_ACTIVE,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]),
        ];

        $response = $this->getJson('/api/orders' . '?' . http_build_query($filters));

        $response->assertStatus(200);
    }

    public function test_order_can_be_created():void
    {
        $products = Product::query()
            ->whereHas('stocks')
            ->inRandomOrder()->take(rand(1, 5))->get();
        $payload = [];
        foreach ($products as $product) {
            $payload[] = [
                'product_id' => $product->id,
                'count' => rand(1, 20),
            ];
        }
        $response = $this->postJson('/api/orders', [
            'customer' => fake()->name,
            'order_items' => $payload,
        ]);

        $response->assertCreated();
    }

    public function test_order_can_be_completed(): void
    {
        $order = Order::query()->where('status', Order::STATUS_ACTIVE)->inRandomOrder()->first();
        $response = $this->postJson('/api/orders/' . $order->id . '/complete');

        $response->assertOk();
    }

    public function test_order_can_be_canceled(): void
    {
        $order = Order::query()->where('status', Order::STATUS_ACTIVE)->inRandomOrder()->first();
        $response = $this->putJson('/api/orders/' . $order->id . '/cancel');

        $response->assertOk();
    }

    public function test_order_can_be_restored_successfully(): void
    {
        $order = Order::query()
            ->where('status', Order::STATUS_CANCELLED)
            ->inRandomOrder()
            ->first();

        $product = Product::query()->find($order->product->first()->id);

        $response = $this->putJson('/api/orders/' . $order->id . '/restore');
        if($product->stocks->sum('stock') < $order->product->first()->pivot->count)
            $response->assertStatus(400);
        else
            $response->assertOk();
    }

    public function test_order_can_be_updated(): void
    {
        $order = Order::query()
            ->where('status', Order::STATUS_ACTIVE)
            ->inRandomOrder()
            ->first();

        $products = Product::query()->inRandomOrder()->first();

        $count = rand(1, 20);
        $response = $this->putJson('/api/orders/'.$order->id.'/update', [
            'customer' => fake()->name,
            'order_item' => [
                'product_id' => $products->id,
                'count' => $count,
            ],
        ]);

        if($products->stocks->sum('stock') < $count)
            $response->assertStatus(400);
        else
            $response->assertOk();
    }
}
