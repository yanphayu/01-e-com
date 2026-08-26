<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'is_read' => $this->is_read,
            'read_at' => $this->whenNotNull($this->read_at?->toISOString()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
