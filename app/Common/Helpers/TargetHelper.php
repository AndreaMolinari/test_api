<?php

namespace App\Common\Helpers;

class TargetHelper {
    /**
     * @param int[][][] $polygon Array di poligoni fatti da array di punti
     * @param float[] $point Longitudine, Latitudine
     */
    public static function isInsideFeature(object $feature, array $point) {
        $inside = false;
        $isCircle = isset($feature->properties->radius) && $feature->geometry->type === 'Point';

        if ($isCircle)
            return static::isInsideCircle($feature->geometry->coordinates, $feature->properties->radius, $point);

        foreach ($feature->geometry->coordinates as $index => $polygon) {
            $inside = static::isInsidePolygon($polygon, $point);

            // se la geometria ha degli intagli a partire dal secondo poligono invertire il isInside
            if ($index !== 0) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public static function isInsidePolygon(array $polygon, array $point) {
        $inside = false;
        $xp = $point[1];
        $yp = $point[0];
        $verticesCount = count($polygon);

        for ($i = 0, $j = $verticesCount - 1; $i < $verticesCount; $j = $i++) {
            $xi = $polygon[$i][1];
            $yi = $polygon[$i][0];
            $xj = $polygon[$j][1];
            $yj = $polygon[$j][0];

            if ((($yi > $yp) !== ($yj > $yp)) && ($xp < ($xj - $xi) * ($yp - $yi) / ($yj - $yi) + $xi))
                $inside = !$inside;
        }

        return $inside;
    }

    public static function isInsideCircle(array $center, float $radius, array $point) {
        // $distanceFromCenter = static::distanceBetweenPoints($center, $point);
        $distanceFromCenter = static::haversineGreatCircleDistance($center[1], $center[0], $point[1], $point[0]);
        return $distanceFromCenter <= $radius;
    }

    // public static function distanceBetweenPoints(array $a, array $b)
    // {
    //     $R = 6378137; // raggio della terra
    //     $dLat = static::toRadians($b[1] - $a[1]);
    //     $dLng = static::toRadians($b[0] - $a[0]);
    //     $x = sin($dLat / 2) * sin($dLat / 2) +
    //         cos(static::toRadians($a[1])) * cos(static::toRadians($b[1])) *
    //         sin($dLng / 2) * sin($dLng / 2);
    //     $y = 2 * atan2(sqrt($x), sqrt(1 - $x));
    //     $z = $R * $y;
    //     return $z;
    // }

    // public static function toRadians($degrees)
    // {
    //     return $degrees * pi() / 180;
    // }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function haversineGreatCircleDistance(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo, float $earthRadius = 6371000): float {
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
}
