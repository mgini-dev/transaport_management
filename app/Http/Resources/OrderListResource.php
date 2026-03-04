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
        $canViewDistance = $request->user()->can('viewDistance', $this->resource);
        $canUpdateStatus = $request->user()->can('updateStatus', $this->resource);
        $canCompleteTransport = $request->user()->can('completeTransportation', $this->resource);
        $canManageLegs = $request->user()->can('manageLegs', $this->resource)
            && in_array($this->status, ['created', 'processing', 'assigned'], true);

        return [
            'id' => EncryptedId::encode($this->id),
            'order_number' => $this->order_number,
            'trip_number' => $this->trip?->trip_number,
            'customer' => $this->customer?->name,
            'status' => $this->status,
            'created_at_label' => optional($this->created_at)?->format('d M Y, h:i A'),
            'distance_km' => $canViewDistance ? $this->distance_km : null,
            'can_view_distance' => $canViewDistance,
            'legs_url' => route('orders.legs.index', EncryptedId::encode($this->id)),
            'show_url' => route('orders.show', EncryptedId::encode($this->id)),
            'status_update_url' => route('orders.status.update', EncryptedId::encode($this->id)),
            'can_start_processing' => $canUpdateStatus && $this->status === 'created',
            'can_end_order' => $canCompleteTransport && $this->status === 'transportation',
            'end_order_url' => route('orders.show', EncryptedId::encode($this->id)).'#transport-actions',
            'can_manage_legs' => $canManageLegs,
        ];
    }
}
