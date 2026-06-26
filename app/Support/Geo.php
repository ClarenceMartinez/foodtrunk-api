<?php

namespace App\Support;

class Geo
{
    /**
     * Distancia en millas entre dos puntos. Misma formula de Haversine
     * que usamos en DiscoverController::nearby(), pero en PHP puro --
     * aqui se calcula para 1 usuario contra unas pocas ubicaciones de
     * UN food truck a la vez (no para miles de filas en una query), asi
     * que no hace falta resolverlo en SQL.
     */
    public static function milesBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusMiles = 3959;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusMiles * $c;
    }
}
