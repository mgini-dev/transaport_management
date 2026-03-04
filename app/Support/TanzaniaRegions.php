<?php

namespace App\Support;

class TanzaniaRegions
{
    /**
     * @return array<string>
     */
    public static function names(): array
    {
        return array_keys(self::coordinates());
    }

    /**
     * @return array<string, array{lat: float, lon: float}>
     */
    public static function coordinates(): array
    {
        return [
            'Arusha' => ['lat' => -3.3869, 'lon' => 36.6830],
            'Dar es Salaam' => ['lat' => -6.7924, 'lon' => 39.2083],
            'Dodoma' => ['lat' => -6.1630, 'lon' => 35.7516],
            'Geita' => ['lat' => -2.8725, 'lon' => 32.2320],
            'Iringa' => ['lat' => -7.7700, 'lon' => 35.6900],
            'Kagera' => ['lat' => -1.3000, 'lon' => 31.2667],
            'Katavi' => ['lat' => -6.3670, 'lon' => 31.0670],
            'Kigoma' => ['lat' => -4.8769, 'lon' => 29.6267],
            'Kilimanjaro' => ['lat' => -3.2500, 'lon' => 37.5000],
            'Lindi' => ['lat' => -10.0000, 'lon' => 39.7167],
            'Manyara' => ['lat' => -4.3167, 'lon' => 36.0833],
            'Mara' => ['lat' => -1.5000, 'lon' => 34.2000],
            'Mbeya' => ['lat' => -8.9094, 'lon' => 33.4608],
            'Morogoro' => ['lat' => -6.8235, 'lon' => 37.6613],
            'Mtwara' => ['lat' => -10.2667, 'lon' => 40.1833],
            'Mwanza' => ['lat' => -2.5164, 'lon' => 32.9175],
            'Njombe' => ['lat' => -9.3500, 'lon' => 34.7667],
            'Pemba North' => ['lat' => -5.1000, 'lon' => 39.8000],
            'Pemba South' => ['lat' => -5.3000, 'lon' => 39.7000],
            'Pwani' => ['lat' => -7.3238, 'lon' => 38.8205],
            'Rukwa' => ['lat' => -7.9667, 'lon' => 31.6167],
            'Ruvuma' => ['lat' => -10.6833, 'lon' => 35.6500],
            'Shinyanga' => ['lat' => -3.6639, 'lon' => 33.4212],
            'Simiyu' => ['lat' => -2.8333, 'lon' => 34.0833],
            'Singida' => ['lat' => -4.8167, 'lon' => 34.7500],
            'Songwe' => ['lat' => -9.1000, 'lon' => 32.9333],
            'Tabora' => ['lat' => -5.0167, 'lon' => 32.8000],
            'Tanga' => ['lat' => -5.0690, 'lon' => 39.0988],
            'Unguja North' => ['lat' => -5.7500, 'lon' => 39.3000],
            'Unguja South' => ['lat' => -6.3000, 'lon' => 39.3667],
            'Unguja West' => ['lat' => -6.1659, 'lon' => 39.2026],
        ];
    }

    public static function canonical(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $normalized = mb_strtolower(trim(str_replace(', Tanzania', '', $value)));
        foreach (self::names() as $name) {
            if (mb_strtolower($name) === $normalized) {
                return $name;
            }
        }

        return null;
    }
}
