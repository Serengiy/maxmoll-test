<?php

namespace Tests\Feature\API;

use Tests\TestCase;

class WarehouseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_warehouses_can_be_retrieved(): void
    {
        $response = $this->get('/api/warehouses');

        $response->assertStatus(200);
    }
}
