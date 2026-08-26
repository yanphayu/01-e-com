<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'courier_id' => $this->courier_id,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'shipping_fee' => (float) $this->shipping_fee,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_phone' => $this->shipping_phone,
            'notes' => $this->notes,
            'paid_at' => $this->whenNotNull($this->paid_at?->toISOString()),
            'shipped_at' => $this->whenNotNull($this->shipped_at?->toISOString()),
            'delivered_at' => $this->whenNotNull($this->delivered_at?->toISOString()),
            'cancelled_at' => $this->whenNotNull($this->cancelled_at?->toISOString()),
            'cancel_reason' => $this->whenNotNull($this->cancel_reason),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' => new SellerProfileResource($this->whenLoaded('seller')),
            'courier' => new CourierResource($this->whenLoaded('courier')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'delivery' => new DeliveryResource($this->whenLoaded('delivery')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
