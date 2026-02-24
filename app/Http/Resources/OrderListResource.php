<?php

namespace App\Http\Resources;

use App\Support\EncryptedId;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => EncryptedId::encode($this->id),
            'order_number' => $this->order_number,
            'trip_number' => $this->trip?->trip_number,
            'customer' => $this->customer?->name,
            'status' => $this->status,
            'distance_km' => $request->user()->can('orders.view_distance') ? $this->distance_km : null,
            'legs_url' => route('orders.legs.index', EncryptedId::encode($this->id)),
            'show_url' => route('orders.show', EncryptedId::encode($this->id)),
            'can_manage_legs' => $request->user()->can('fleet.assign'),
        ];
    }
}
