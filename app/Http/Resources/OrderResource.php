<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'count' => $this->product->first()?->pivot?->count,
            'product' => $this->whenLoaded('product', fn() => new ProductResource($this->product->first())),
            'warehouse' => $this->whenLoaded('warehouse', fn() => new WarehouseResource($this->warehouse)),
        ];
    }
}
