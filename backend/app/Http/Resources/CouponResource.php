<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => (float) $this->value,
            'min_order_amount' => (float) $this->min_order_amount,
            'max_discount' => $this->whenNotNull((float) $this->max_discount),
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'is_active' => $this->is_active,
            'starts_at' => $this->whenNotNull($this->starts_at?->toISOString()),
            'expires_at' => $this->whenNotNull($this->expires_at?->toISOString()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
