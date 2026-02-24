<?php

namespace App\Http\Resources;

use App\Support\EncryptedId;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Trip */
class TripListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canClose = $request->user()->can('trips.close')
            && ($request->user()->can('trips.view_all') || $this->created_by === $request->user()->id);

        return [
            'id' => EncryptedId::encode($this->id),
            'trip_number' => $this->trip_number,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'show_url' => route('trips.show', EncryptedId::encode($this->id)),
            'close_url' => route('trips.close', EncryptedId::encode($this->id)),
            'can_close' => $canClose,
        ];
    }
}
