<?php

namespace App\Support;

class OfficeLocation
{
    public static function get(): array
    {
        $label = (string) config('office.label', 'Lokasi Kantor');
        $lat = config('office.latitude') ?: env('OFFICE_LATITUDE');
        $lng = config('office.longitude') ?: env('OFFICE_LONGITUDE');
        $accuracy = config('office.accuracy') ?: env('OFFICE_ACCURACY', 25);

        $latFloat = is_numeric($lat) ? (float) $lat : null;
        $lngFloat = is_numeric($lng) ? (float) $lng : null;
        $isConfigured = $latFloat !== null
            && $lngFloat !== null
            && $latFloat >= -90
            && $latFloat <= 90
            && $lngFloat >= -180
            && $lngFloat <= 180;

        $lokasiString = null;
        if ($isConfigured) {
            $lokasiString = number_format($latFloat, 7, '.', '').', '.number_format($lngFloat, 7, '.', '');
        }

        return [
            'label' => $label,
            'configured' => $isConfigured,
            'lat' => $isConfigured ? $latFloat : null,
            'lng' => $isConfigured ? $lngFloat : null,
            'accuracy' => is_numeric($accuracy) ? (int) round((float) $accuracy) : null,
            'lokasi_string' => $lokasiString,
        ];
    }
}
