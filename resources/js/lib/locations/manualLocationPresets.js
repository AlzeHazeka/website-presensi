function formatLatLng(lat, lng) {
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return '';
    return `${lat.toFixed(7)}, ${lng.toFixed(7)}`;
}

/**
 * Preset lokasi untuk mempercepat input manual (client-side).
 * NOTE: Ini bukan data rahasia; akan masuk ke bundle frontend.
 */
export const MANUAL_LOCATION_PRESETS = [
    {
        id: 'office_default',
        label: 'Lokasi Kantor (preset)',
        lat: -7.161892,
        lng: 112.621675,
        accuracy: 25,
        lokasi_string: formatLatLng(-7.161892, 112.621675),
    },
];

