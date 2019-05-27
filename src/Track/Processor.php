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

    /**
     * @param Point[] $pointCollection
     * @param float $distance minimum distance between points
     *
     * @return array
     */
    public function generateElevationData(iterable $pointCollection, float $distance = 150): array
    {
        $lastPoint = null;
        $elevationData = [];

        foreach ($pointCollection as $point) {
            if (!$lastPoint || ($point->getDistance() - $lastPoint->getDistance() > $distance)) {
                $elevationData[] = [
                    'elev' => $point->getElevation(),
                    'label' => number_format(
                        $point->getDistance() / 1000,
                        1,
                        '.',
                        ' '
                    ),
                    'point' => $point,
                ];
                $lastPoint = $point;
            }
        }

        return $elevationData;
    }
}
