<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SellerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'store_name' => $this->store_name,
            'store_slug' => $this->store_slug,
            'description' => $this->description,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'rating' => (float) $this->rating,
            'total_sales' => $this->total_sales,
            'is_verified' => $this->is_verified,
            'is_suspended' => $this->is_suspended,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
