<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Support\TanzaniaRegions;
use Throwable;

class DistanceService
{
    public function calculateKm(string $originAddress, string $destinationAddress): ?float
    {
        $regionDistance = $this->calculateUsingTanzaniaRegions($originAddress, $destinationAddress);
        if ($regionDistance !== null) {
            return $regionDistance;
        }

        $apiKey = config('services.distancematrix.key');

        if ($apiKey) {
            $googleDistance = $this->calculateViaGoogle($originAddress, $destinationAddress, $apiKey);
            if ($googleDistance !== null) {
                return $googleDistance;
            }
        }

        return $this->calculateViaOpenStreetMap($originAddress, $destinationAddress);
    }

    private function calculateUsingTanzaniaRegions(string $originAddress, string $destinationAddress): ?float
    {
        $originRegion = TanzaniaRegions::canonical($originAddress);
        $destinationRegion = TanzaniaRegions::canonical($destinationAddress);

        if (! $originRegion || ! $destinationRegion) {
            return null;
        }

        if ($originRegion === $destinationRegion) {
            return 0.0;
        }

        $coordinates = TanzaniaRegions::coordinates();
        $origin = $coordinates[$originRegion] ?? null;
        $destination = $coordinates[$destinationRegion] ?? null;
        if (! $origin || ! $destination) {
            return null;
        }

        $airDistance = $this->calculateHaversineKm(
            (float) $origin['lat'],
            (float) $origin['lon'],
            (float) $destination['lat'],
            (float) $destination['lon']
        );

        if ($airDistance === null) {
            return null;
        }

        // Road distance is usually longer than straight-line distance.
        return round($airDistance * 1.25, 2);
    }

    private function calculateViaGoogle(string $originAddress, string $destinationAddress, string $apiKey): ?float
    {
        try {
            $response = Http::timeout(10)
                ->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => $originAddress,
                    'destinations' => $destinationAddress,
                    'units' => 'metric',
                    'key' => $apiKey,
                ]);
        } catch (Throwable $exception) {
            Log::warning('Google Distance Matrix request failed.', [
                'error' => $exception->getMessage(),
            ]);
            return null;
        }

        if (! $response->ok()) {
            Log::warning('Google Distance Matrix returned non-success HTTP status.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $status = data_get($response->json(), 'status');
        $elementStatus = data_get($response->json(), 'rows.0.elements.0.status');
        if ($status !== 'OK' || ($elementStatus && $elementStatus !== 'OK')) {
            Log::warning('Google Distance Matrix returned non-OK payload status.', [
                'status' => $status,
                'element_status' => $elementStatus,
            ]);
            return null;
        }

        $meters = data_get($response->json(), 'rows.0.elements.0.distance.value');
        return is_numeric($meters) ? round(((float) $meters) / 1000, 2) : null;
    }

    private function calculateViaOpenStreetMap(string $originAddress, string $destinationAddress): ?float
    {
        $osrmBaseUrl = rtrim((string) config('services.distancematrix.osrm_url', 'https://router.project-osrm.org'), '/');

        $origin = $this->geocodeAddress($originAddress);
        $destination = $this->geocodeAddress($destinationAddress);

        if (! $origin || ! $destination) {
            Log::warning('Distance geocoding failed for one or both addresses.', [
                'origin_address' => $originAddress,
                'destination_address' => $destinationAddress,
            ]);
            return null;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => $this->userAgent(),
                ])
                ->get($osrmBaseUrl.'/route/v1/driving/'
                    .$origin['lon'].','.$origin['lat'].';'
                    .$destination['lon'].','.$destination['lat'], [
                        'overview' => 'false',
                        'alternatives' => 'false',
                        'steps' => 'false',
                    ]);

            if ($response->ok()) {
                $meters = data_get($response->json(), 'routes.0.distance');
                if (is_numeric($meters)) {
                    return round(((float) $meters) / 1000, 2);
                }
            } else {
                Log::warning('OSRM routing returned non-success HTTP status.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('OSRM routing request failed.', [
                'error' => $exception->getMessage(),
            ]);
        }

        // Fallback if OSRM is down/rate-limited: approximate straight-line distance.
        $haversineKm = $this->calculateHaversineKm(
            (float) $origin['lat'],
            (float) $origin['lon'],
            (float) $destination['lat'],
            (float) $destination['lon']
        );

        return $haversineKm !== null ? round($haversineKm, 2) : null;
    }

    /**
     * @return array{lat:string, lon:string}|null
     */
    private function geocodeAddress(string $address): ?array
    {
        $nominatimBaseUrl = rtrim((string) config('services.distancematrix.nominatim_url', 'https://nominatim.openstreetmap.org'), '/');
        $queryAddress = str_contains(strtolower($address), 'tanzania')
            ? $address
            : $address.', Tanzania';

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => $this->userAgent(),
                ])
                ->get($nominatimBaseUrl.'/search', [
                    'q' => $queryAddress,
                    'format' => 'jsonv2',
                    'limit' => 1,
                ]);
        } catch (Throwable $exception) {
            Log::warning('Nominatim geocode request failed.', [
                'address' => $address,
                'error' => $exception->getMessage(),
            ]);
            return null;
        }

        if (! $response->ok()) {
            Log::warning('Nominatim geocode returned non-success HTTP status.', [
                'address' => $address,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $first = data_get($response->json(), '0');
        $lat = data_get($first, 'lat');
        $lon = data_get($first, 'lon');

        if (! is_string($lat) || ! is_string($lon)) {
            return null;
        }

        return ['lat' => $lat, 'lon' => $lon];
    }

    private function calculateHaversineKm(float $lat1, float $lon1, float $lat2, float $lon2): ?float
    {
        if (! is_finite($lat1) || ! is_finite($lon1) || ! is_finite($lat2) || ! is_finite($lon2)) {
            return null;
        }

        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function userAgent(): string
    {
        $default = Str::slug((string) config('app.name', 'transport-system'), '-').'/1.0';
        return (string) config('services.distancematrix.user_agent', $default);
    }
}
