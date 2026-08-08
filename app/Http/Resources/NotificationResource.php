<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->data['titre'] ?? null,
            'message' => $this->data['message'] ?? null,
            'trip_id' => $this->data['trip_id'] ?? null,
            'lu' => ! is_null($this->read_at),
            'created_at' => $this->created_at,
        ];
    }
}
