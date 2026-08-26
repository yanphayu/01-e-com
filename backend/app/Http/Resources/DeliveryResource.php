<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'courier_id' => $this->courier_id,
            'status' => $this->status,
            'fee' => (float) $this->fee,
            'pickup_address' => $this->pickup_address,
            'delivery_address' => $this->delivery_address,
            'picked_up_at' => $this->whenNotNull($this->picked_up_at?->toISOString()),
            'delivered_at' => $this->whenNotNull($this->delivered_at?->toISOString()),
            'proof_image' => $this->proof_image,
            'courier' => new CourierResource($this->whenLoaded('courier')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
