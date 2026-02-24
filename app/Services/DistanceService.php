<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DistanceService
{
    public function calculateKm(string $originAddress, string $destinationAddress): ?float
    {
        $apiKey = config('services.distancematrix.key');

        if (! $apiKey) {
            return null;
        }

        $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $originAddress,
            'destinations' => $destinationAddress,
            'units' => 'metric',
            'key' => $apiKey,
        ]);

        if (! $response->ok()) {
            return null;
        }

        $meters = data_get($response->json(), 'rows.0.elements.0.distance.value');

        if (! is_numeric($meters)) {
            return null;
        }

        return round(((float) $meters) / 1000, 2);
    }
}

