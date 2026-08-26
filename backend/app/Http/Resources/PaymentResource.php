<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'paid_at' => $this->whenNotNull($this->paid_at?->toISOString()),
            'refunded_at' => $this->whenNotNull($this->refunded_at?->toISOString()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
