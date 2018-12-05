<?php

namespace App\Track;

use App\Entity\Gps;
use App\Entity\Gps\Point;
use App\Entity\Gps\OptimizedPoint;

class Processor
{
    public function process(string $source, Gps $gps): Gps
    {
        $xml = simplexml_load_string($source);
        if ($xml === false) {
            throw new \RuntimeException("Xml load failed");
        }

        foreach ($xml->trk as $track) {
            $order = 0;
            $optimizedPointLat = 0;
            $optimizedPointLon = 0;
            foreach ($track->trkseg as $trackSegment) {
                foreach ($trackSegment->trkpt as $point) {
                    $attributes = $point->attributes();
                    $lat = (float)$attributes['lat'];
                    $lon = (float)$attributes['lon'];

                    $point = new Point(
                        $order,
                        $lat,
                        $lon
                    );

                    $gps->addPoint($point);

                    $latDiff = $optimizedPointLat - $lat;
                    $lonDiff = $optimizedPointLon - $lon;
                    $diff = abs($latDiff) + abs($lonDiff);
                    if ($diff > 0.0020) {
                        $optimizedPointLat = $lat;
                        $optimizedPointLon = $lon;
                        $optimizedPoint = new OptimizedPoint(
                            $order,
                            $lat,
                            $lon
                        );

                        $gps->addOptimizedPoint($optimizedPoint);
                    }

                    $order++;
                }
            }
        }

        return $gps;
    }
}