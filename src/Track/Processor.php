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
        $xml = simplexml_load_string($source);
        if ($xml === false) {
            throw new \RuntimeException("Xml load failed");
        }

        foreach ($xml->trk as $track) {
            $order = 0;

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

                    $version->addPoint($point);

                    $order++;
                }
            }
        }
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
}
