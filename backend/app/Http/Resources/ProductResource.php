<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'compare_price' => $this->whenNotNull((float) $this->compare_price),
            'stock' => $this->stock,
            'sold_count' => $this->sold_count,
            'view_count' => $this->view_count,
            'weight' => $this->whenNotNull($this->weight),
            'condition' => $this->condition,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'location' => $this->location,
            'city' => $this->city,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'seller' => new SellerProfileResource($this->whenLoaded('seller')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'reviews_count' => $this->whenCounted('reviews'),
            'average_rating' => $this->when(isset($this->average_rating), (float) $this->average_rating),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
