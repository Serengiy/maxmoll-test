<?php

namespace Tests\Feature\API;

use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_products_can_be_retrieved(): void
    {
        $page = rand(1, 5);
        $include = 'stocks';
        $paginate = 20;
        $response = $this->getJson('/api/products?' . http_build_query(
            compact('include', 'paginate', 'page')
        ));

        $response->assertStatus(200);
    }
}
