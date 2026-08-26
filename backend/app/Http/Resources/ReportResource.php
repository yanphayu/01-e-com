<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reporter_id' => $this->reporter_id,
            'reportable_type' => $this->reportable_type,
            'reportable_id' => $this->reportable_id,
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
