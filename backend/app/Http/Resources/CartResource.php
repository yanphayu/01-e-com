<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        $items = $this->whenLoaded('items');

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => CartItemResource::collection($items),
            'total' => (float) ($items->sum(fn ($item) => $item->price * $item->quantity) ?? 0),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
