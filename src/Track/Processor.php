<?php

namespace App\Track;

use App\Entity\Gps;
use App\Entity\Gps\Point;
use App\Entity\Gps\OptimizedPoint;

class Processor
{
    public function process(string $filepath): Gps
    {
        $xml = simplexml_load_file($filepath);
        if ($xml === false) {
            throw new \RuntimeException("Xml load failed");
        }

        $gps = new Gps();

        foreach ($xml->trk as $track) {
            $order = 0;
            $optimizedPointLat = 0;
            $optimizedPointLon = 0;
            foreach ($track->trkseg as $tragSegment) {
                foreach ($tragSegment->trkpt as $point) {
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
                    if ($diff > 0.003) {
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