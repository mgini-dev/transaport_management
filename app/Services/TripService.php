<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Carbon;

class TripService
{
    public function create(User $actor): Trip
    {
        return Trip::query()->create([
            'trip_number' => $this->generateTripNumber(),
            'status' => 'open',
            'created_by' => $actor->id,
        ]);
    }

    public function close(Trip $trip, User $actor): Trip
    {
        if ($trip->status === 'closed') {
            return $trip;
        }

        $trip->update([
            'status' => 'closed',
            'closed_by' => $actor->id,
            'closed_at' => Carbon::now(),
        ]);

        return $trip->refresh();
    }

    private function generateTripNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $sequence = Trip::query()->whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('TRP-%s-%04d', $datePrefix, $sequence);
    }
}

