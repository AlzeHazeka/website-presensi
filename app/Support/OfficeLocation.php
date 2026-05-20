<?php

namespace App\Support;

class OfficeLocation
{
    /**
     * @return array{label:string, lat:float|null, lng:float|null, accuracy:int|null, lokasi_string:?string}
     */
    public static function get(): array
    {
        $label = (string) config('office.label', 'Lokasi Kantor');
        $lat = config('office.latitude');
        $lng = config('office.longitude');
        $accuracy = config('office.accuracy');

        $latFloat = is_numeric($lat) ? (float) $lat : null;
        $lngFloat = is_numeric($lng) ? (float) $lng : null;

        $lokasiString = null;
        if ($latFloat !== null && $lngFloat !== null) {
            $lokasiString = number_format($latFloat, 7, '.', '').', '.number_format($lngFloat, 7, '.', '');
        }

        return [
            'label' => $label,
            'lat' => $latFloat,
            'lng' => $lngFloat,
            'accuracy' => is_numeric($accuracy) ? (int) round((float) $accuracy) : null,
            'lokasi_string' => $lokasiString,
        ];
    }
}

