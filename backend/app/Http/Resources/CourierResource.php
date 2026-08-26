<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_plate' => $this->vehicle_plate,
            'phone' => $this->phone,
            'current_location' => $this->current_location,
            'latitude' => $this->whenNotNull($this->latitude),
            'longitude' => $this->whenNotNull($this->longitude),
            'is_online' => $this->is_online,
            'is_available' => $this->is_available,
            'rating' => (float) $this->rating,
            'total_deliveries' => $this->total_deliveries,
            'total_earnings' => (float) $this->total_earnings,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
