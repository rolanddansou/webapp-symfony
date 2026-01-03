<?php

namespace App\Feature\Helper;

class GpsHelper
{
    public static function haversineGreatCircleDistance(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo,
        float $earthRadius = 6371.0
    ): float {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public static function isValidGpsCoordinate(float $latitude, float $longitude): bool
    {
        return $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180;
    }

    public static function isValidGpsPoint(string $gpsPoint)
    {
        $parts = explode(',', $gpsPoint);
        if (count($parts) !== 2) {
            return false;
        }

        $latitude = floatval(trim($parts[0]));
        $longitude = floatval(trim($parts[1]));

        return self::isValidGpsCoordinate($latitude, $longitude);
    }

    /**
     * @param string $gpsPoint
     * @return array
     * to be used like:
     * $coords = GpsHelper::parseGpsPoint("48.8566, 2.3522");
     * $latitude = $coords['latitude'];
     * $longitude = $coords['longitude'];
     * or
     * ['latitude' => $latitude, 'longitude' => $longitude] = GpsHelper::parseGpsPoint("48.8566, 2.3522");
     */
    public static function parseGpsPoint(string $gpsPoint)
    {
        $parts = explode(',', $gpsPoint);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Invalid GPS point format.');
        }

        $latitude = floatval(trim($parts[0]));
        $longitude = floatval(trim($parts[1]));

        if (!self::isValidGpsCoordinate($latitude, $longitude)) {
            throw new \InvalidArgumentException('Invalid GPS coordinates.');
        }

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }
}
