<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->whenNotNull($this->first_name),
            'last_name' => $this->whenNotNull($this->last_name),
            'email' => $this->email,
            'phone' => $this->whenNotNull($this->phone),
            'avatar' => $this->whenNotNull($this->avatar),
            'facebook' => $this->whenNotNull($this->facebook),
            'instagram' => $this->whenNotNull($this->instagram),
            'twitter' => $this->whenNotNull($this->twitter),
            'email_verified_at' => $this->whenNotNull($this->email_verified_at?->toISOString()),
            'is_active' => $this->is_active,
            'is_suspended' => $this->is_suspended,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'seller_profile' => new SellerProfileResource($this->whenLoaded('sellerProfile')),
            'courier' => new CourierResource($this->whenLoaded('courier')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
