<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'stock' => $this->stock,
            'product' => $this->whenLoaded('product', fn() => new ProductResource($this->product)),
            'ware_house' => $this->whenLoaded('wareHouse', fn() => new WareHouseResource($this->wareHouse)),
        ];
    }
}
