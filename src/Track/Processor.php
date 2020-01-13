<?php

namespace App\Track;

use App\Entity\Track;
use App\Entity\Track\Point;
use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Version;
use App\Track\ElevationNoiseReduction\ElevationNoiseReducerInterface;

class Processor
{
    /**
     * @var TwoPointsChecker\PointCheckerInterface[]
     */
    private $twoPointsCheckers = [];

    /**
     * @var ElevationNoiseReducerInterface[]
     */
    private $elevationNoceReducer = [];

    public function addTwoPointsChecker(TwoPointsChecker\PointCheckerInterface $checker)
    {
        $this->twoPointsCheckers[] = $checker;
    }

    public function addElevationNoiseReducer(ElevationNoiseReducerInterface $reducer)
    {
        $this->elevationNoceReducer[] = $reducer;
    }

    private function isPointReal(Point $pointA, Point $pointB): bool
    {
        foreach ($this->twoPointsCheckers as $singlePointChecker) {
            if (!$singlePointChecker->isReal($pointA, $pointB)) {
                return false;
            }
        }

        return true;
    }

    public function process(string $source, Version $version)
    {
        $version->getPoints()->clear();

        $xml = simplexml_load_string($source);
        if ($xml === false) {
            throw new \RuntimeException("Xml load failed");
        }

        if ($xml->getName() != "gpx") {
            throw new \RuntimeException("Xml invalid format");
        }

        $previousPoint = null;
        $order = 0;
        $positiveElevation = 0;
        $negativeElevation = 0;
        $version->getWayPoints()->clear();

        foreach ($xml->trk as $track) {
            foreach ($track->trkseg as $trackSegment) {
                /** @var Point */
                $previousElevationPoint = null;
                /** @var Point */
                $previousRealPoint = null;
                foreach ($trackSegment->trkpt as $trackPoint) {
                    $attributes = $trackPoint->attributes();

                    /**
                     * Longitude and latitude are requered attributes.
                     * Skip if one or both of them are missing.
                     */
                    if (!isset($attributes['lat']) || !isset($attributes['lon'])) {
                        /* TODO: Throw an exception for corrupted GPX file? */
                        continue;
                    }

                    $lat = (float)$attributes['lat'];
                    $lon = (float)$attributes['lon'];

                    /**
                     * Latitude must be bigger than -90 and less than 90
                     * degrees. If the value is outside, asume there is
                     * something wrong with the gpx file and throw an
                     * exception.
                     * Same applies for longtitude, but the range is -180, 180.
                     */
                    if ($lat < -90 || $lat > 90) {
                        throw new \UnexpectedValueException("Invalid latitude value " . $lat);
                    }

                    if ($lon < -180 || $lon > 180) {
                        throw new \UnexpectedValueException("Invalid longitude value " . $lon);
                    }

                    $point = new Point(
                        $order,
                        $lat,
                        $lon
                    );

                    if ($trackPoint->ele) {
                        $point->setElevation(floatval($trackPoint->ele));
                    }

                    $elevationChange = 0;
                    if ($previousPoint) {
                        $distance = $point->distance($previousPoint);
                        $point->setDistance($distance + $previousPoint->getDistance());

                        if ($previousElevationPoint && $point->getElevation()) {
                            if ($point->getElevation() > $previousElevationPoint->getElevation()) {
                                $elevationChange = $point->getElevation() - $previousElevationPoint->getElevation();
                                if ($previousRealPoint === null || $this->isPointReal($previousRealPoint, $point)) {
                                    $previousRealPoint = $point;
                                } else {
                                    $point->setElevation();
                                }
                            } else {
                                $elevationChange = $previousElevationPoint->getElevation() - $point->getElevation();
                                if ($previousRealPoint === null || $this->isPointReal($previousRealPoint, $point)) {
                                    $previousRealPoint = $point;
                                } else {
                                    $point->setElevation();
                                }
                            }
                        }
                    }

                    $version->addPoint($point);

                    $order++;

                    if ($point->getElevation()) {
                        if ($previousElevationPoint === null) {
                            $previousElevationPoint = $point;
                        } elseif ($elevationChange !== 0) {
                            $addElevation = true;
                            foreach ($this->elevationNoceReducer as $noiseReducer) {
                                if (!$noiseReducer->shouldCount($previousElevationPoint, $point)) {
                                    $addElevation = false;
                                    break;
                                }
                            }

                            if ($addElevation) {
                                if ($point->getElevation() > $previousElevationPoint->getElevation()) {
                                    $positiveElevation += $elevationChange;
                                } else {
                                    $negativeElevation += $elevationChange;
                                }

                                $previousElevationPoint = $point;
                            }
                        }
                    }

                    $previousPoint = $point;
                }
            }
        }

        $version->setPositiveElevation($positiveElevation);
        $version->setNegativeElevation($negativeElevation);
        
        // way points
        foreach ($xml->wpt as $wayPointXml) {
            $wayPointXmlAttr = $wayPointXml->attributes();
            $wayPoint = new Track\WayPoint((float)$wayPointXmlAttr['lat'], (float)$wayPointXmlAttr['lon']);

            if ($wayPointXml->ele) {
                $wayPoint->setElevation(floatval($wayPointXml->ele));
            }

            if ($wayPointXml->name) {
                $wayPoint->setName($wayPointXml->name);
            }
            $version->addWayPoint($wayPoint);
        }
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
}
