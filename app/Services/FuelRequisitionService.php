<?php

namespace App\Services;

use App\Models\FuelRequisition;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FuelRequisitionService
{
    public function __construct(private readonly NotificationRoutingService $notificationRoutingService)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): FuelRequisition
    {
        return DB::transaction(function () use ($payload, $actor) {
            $order = \App\Models\Order::query()->with('trip')->findOrFail((int) $payload['order_id']);

            if ($order->trip->status !== 'open') {
                throw new \RuntimeException('Cannot create fuel requisition for an order in a closed trip.');
            }

            $litres = (float) $payload['additional_litres'];
            $price = (float) $payload['fuel_price'];
            $discount = (float) ($payload['discount'] ?? 0);

            $requisition = FuelRequisition::query()->create([
                ...Arr::only($payload, [
                    'order_id',
                    'fleet_id',
                    'fuel_station',
                    'additional_litres',
                    'fuel_price',
                    'discount',
                    'payment_channel',
                    'payment_account',
                ]),
                'requested_by' => $actor->id,
                'total_amount' => ($litres * $price) - $discount,
                'status' => 'submitted',
            ]);

            $this->notificationRoutingService->notifyPermission(
                permission: 'fuel.approve.supervisor',
                title: 'Fuel requisition needs supervisor review',
                message: "Fuel requisition #{$requisition->id} has been submitted.",
                type: 'fuel.submitted',
                meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id]
            );

            return $requisition;
        });
    }

    public function supervisorDecision(FuelRequisition $requisition, bool $approved, User $actor, ?string $remarks = null): FuelRequisition
    {
        if ($requisition->status !== 'submitted') {
            return $requisition;
        }

        $requisition->update([
            'status' => $approved ? 'supervisor_approved' : 'supervisor_rejected',
            'supervisor_id' => $actor->id,
            'supervisor_remarks' => $remarks,
            'supervisor_reviewed_at' => now(),
        ]);

        if ($approved) {
            $this->notificationRoutingService->notifyPermission(
                permission: 'fuel.approve.accounting',
                title: 'Fuel requisition needs accounting approval',
                message: "Fuel requisition #{$requisition->id} was approved by supervisor.",
                type: 'fuel.supervisor_approved',
                meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id]
            );
        } else {
            $requisition->requester?->notify(new \App\Notifications\WorkflowStageNotification(
                title: 'Fuel requisition rejected by supervisor',
                message: "Fuel requisition #{$requisition->id} was rejected by supervisor.",
                type: 'fuel.supervisor_rejected',
                meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id]
            ));
        }

        return $requisition->refresh();
    }

    public function accountantDecision(FuelRequisition $requisition, bool $approved, User $actor, ?string $remarks = null): FuelRequisition
    {
        if ($requisition->status !== 'supervisor_approved') {
            return $requisition;
        }

        $requisition->update([
            'status' => $approved ? 'accountant_approved' : 'accountant_rejected',
            'accountant_id' => $actor->id,
            'accountant_remarks' => $remarks,
            'accountant_reviewed_at' => now(),
        ]);

        $requisition->requester?->notify(new \App\Notifications\WorkflowStageNotification(
            title: 'Fuel requisition accounting decision',
            message: "Fuel requisition #{$requisition->id} is now {$requisition->status}.",
            type: 'fuel.accountant_decision',
            meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id]
        ));

        return $requisition->refresh();
    }
}
