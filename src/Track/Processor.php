<?php

namespace App\Track;

use App\Entity\Track;
use App\Entity\Track\Point;
use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Version;

class Processor
{
    public function process(string $source, Version $version)
    {
        $version->getPoints()->clear();

        $xml = simplexml_load_string($source);
        if ($xml === false) {
            throw new \RuntimeException("Xml load failed");
        }

        $previousPoint = null;
        $order = 0;
        $positiveElevation = 0;
        $negativeElevation = 0;

        foreach ($xml->trk as $track) {
            foreach ($track->trkseg as $trackSegment) {
                foreach ($trackSegment->trkpt as $trackPoint) {
                    $attributes = $trackPoint->attributes();
                    $lat = (float)$attributes['lat'];
                    $lon = (float)$attributes['lon'];

                    $point = new Point(
                        $order,
                        $lat,
                        $lon
                    );

                    if ($trackPoint->ele) {
                        $point->setElevation(floatval($trackPoint->ele));
                    }

                    if ($previousPoint) {
                        $distance = $this->calculateDistance($point, $previousPoint);
                        $point->setDistance($distance + $previousPoint->getDistance());

                        if ($point->getElevation() > $previousPoint->getElevation()) {
                            $positiveElevation += $point->getElevation() - $previousPoint->getElevation();
                        } else {
                            $negativeElevation += $previousPoint->getElevation() - $point->getElevation();
                        }

                    }

                    $version->addPoint($point);

                    $order++;

                    $previousPoint = $point;
                }
            }
        }

        $version->setPositiveElevation($positiveElevation);
        $version->setNegativeElevation($negativeElevation);
    }

    private function calculateDistance(Point $a, Point $b)
    {
        $r = 6371000;

        $dLan = deg2rad($b->getLat() - $a->getLat());
        $dLng = deg2rad($b->getLng() - $a->getLng());

        $lat1 = deg2rad($a->getLat());
        $lat2 = deg2rad($b->getLat());

        $a = (sin($dLan / 2) ** 2) + (sin($dLng / 2) ** 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $c * $r;
    }

    /**
     * @param Version $version
     * @return OptimizedPoint[]
     */
    public function generateOptimizedPoints(Version $version)
    {
        $optimizedPointLat = 0;
        $optimizedPointLon = 0;

        $order = 0;

        $optimizedPointCollection = [];

        foreach ($version->getPoints() as $point) {
            $latDiff = $optimizedPointLat - $point->getLat();
            $lonDiff = $optimizedPointLon - $point->getLng();
            $diff = abs($latDiff) + abs($lonDiff);
            if ($diff > 0.0020) {
                $optimizedPointLat = $point->getLat();
                $optimizedPointLon = $point->getLng();
                $optimizedPoint = new OptimizedPoint(
                    $order,
                    $point->getLat(),
                    $point->getLng()
                );

                $optimizedPointCollection[] = $optimizedPoint;
            }

            $order++;
        }

        return $optimizedPointCollection;
    }

    public function postProcess(Track $track)
    {
        $track->recalculateEdgesCache();
    }

    public function generateElevationLables(iterable $pointCollection, int $pointsCount)
    {
        $longestDistance = 0;
        $labels = [];

        foreach ($pointCollection as $points) {
            $lastPoint = end($points);
            if ($lastPoint->getDistance() > $longestDistance) {
                $longestDistance = $lastPoint->getDistance();
            }
        }

        $labelDistance = $longestDistance / $pointsCount;
        for ($q = 0; $q < $pointsCount; $q++) {
            $labels[] = $labelDistance * $q;
        }

        return $labels;
    }

    /**
     * @param Point[][] $pointCollection
     * @param iterable $lables
     *
     * @return array
     */
    public function generateElevationData(iterable $pointCollection, iterable $lables): array
    {
        $return = [];

        foreach ($pointCollection as $item) {
            reset($item);
            $return[] = [];
        }

        $collectionsCount = count($pointCollection);

        foreach ($lables as $labelIndex => $labelDistance) {
            for($q = 0; $q < $collectionsCount; $q++) {
                $currentPoint = current($pointCollection[$q]);

                // case: skip point
                while ($currentPoint && $currentPoint->getDistance() < $labelDistance) {
                    $currentPoint = next($pointCollection[$q]);
                }

                if ($currentPoint === false) {
                    continue;
                }

                // case: skip label
                if (isset($lables[$labelIndex + 1])) {
                    $nextLabelDistance = $lables[$labelIndex + 1];
                    if ($nextLabelDistance < $currentPoint->getDistance()) {
                        $return[$q][] = $currentPoint->getElevation();
                        continue;
                    }
                }


                $return[$q][] = $currentPoint->getElevation();
                next($pointCollection[$q]);
            }
        }

        return $return;
    }
}
