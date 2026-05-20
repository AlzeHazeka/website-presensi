<?php

namespace App\Support;

/**
 * Extract lokasi/position payload secara konsisten untuk presensi dan lembur.
 *
 * - Backward compatible: tetap bisa menerima string "lokasi" berupa "lat, lng".
 * - Tidak memaksa lat/lng tersedia (nullable).
 * - accuracy dinormalisasi ke integer (meter).
 */
class GeoPosition
{
    /**
     * @param  array{lokasi?:mixed, latitude?:mixed, longitude?:mixed, accuracy?:mixed}  $validated
     * @return array{lat:float|null, lng:float|null, accuracy:int|null, lokasi_string:?string}
     */
    public static function extract(array $validated): array
    {
        $lat = array_key_exists('latitude', $validated) ? $validated['latitude'] : null;
        $lng = array_key_exists('longitude', $validated) ? $validated['longitude'] : null;
        $accuracy = array_key_exists('accuracy', $validated) ? $validated['accuracy'] : null;

        if (is_numeric($lat) && is_numeric($lng)) {
            $lat = (float) $lat;
            $lng = (float) $lng;

            $lokasiString = number_format($lat, 7, '.', '').', '.number_format($lng, 7, '.', '');

            return [
                'lat' => $lat,
                'lng' => $lng,
                'accuracy' => is_numeric($accuracy) ? (int) round((float) $accuracy) : null,
                'lokasi_string' => $lokasiString,
            ];
        }

        $lokasiRaw = isset($validated['lokasi']) ? trim((string) $validated['lokasi']) : '';
        $lokasiString = $lokasiRaw !== '' ? $lokasiRaw : null;

        // Coba parse "lat, lng" jika ada (legacy payload)
        if ($lokasiString && str_contains($lokasiString, ',')) {
            [$latPart, $lngPart] = array_map('trim', explode(',', $lokasiString, 2));
            if (is_numeric($latPart) && is_numeric($lngPart)) {
                $latFloat = (float) $latPart;
                $lngFloat = (float) $lngPart;
                if ($latFloat >= -90 && $latFloat <= 90 && $lngFloat >= -180 && $lngFloat <= 180) {
                    return [
                        'lat' => $latFloat,
                        'lng' => $lngFloat,
                        'accuracy' => is_numeric($accuracy) ? (int) round((float) $accuracy) : null,
                        'lokasi_string' => $lokasiString,
                    ];
                }
            }
        }

        return [
            'lat' => null,
            'lng' => null,
            'accuracy' => is_numeric($accuracy) ? (int) round((float) $accuracy) : null,
            'lokasi_string' => $lokasiString,
        ];
    }
}

