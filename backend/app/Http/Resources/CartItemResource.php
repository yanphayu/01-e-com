<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->whenLoaded('product')),
            'subtotal' => (float) ($this->whenLoaded('product') ? $this->product->price * $this->quantity : 0),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
