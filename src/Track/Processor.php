<?php

namespace App\Track;

use App\Entity\Track;
use App\Entity\Track\Point;
use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Version;

class Processor
{
    /**
     * @var SinglePointChecker\PointCheckerInterface[]
     */
    private $singlePointCheckers = [];

    public function addSinglePointChecker(SinglePointChecker\PointCheckerInterface $checker) {
        $this->singlePointCheckers[] = $checker;
    }

    private function isPointReal($distance, $elevation): bool
    {
        foreach ($this->singlePointCheckers as $singlePointChecker) {
            if (!$singlePointChecker->isReal($distance, $elevation)) {
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
                $previousElevation = null;
                $distanceAddedFromPreviousRealPoint = 0;
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

                    if ($previousPoint) {
                        $distance = $this->calculateDistance($point, $previousPoint);
                        $point->setDistance($distance + $previousPoint->getDistance());

                        if ($previousElevation && $point->getElevation()) {
                            if ($point->getElevation() > $previousElevation) {
                                $elevationChange = $point->getElevation() - $previousElevation;
                                if ($this->isPointReal($distance + $distanceAddedFromPreviousRealPoint, $elevationChange)) {
                                    $positiveElevation += $elevationChange;
                                    $distanceAddedFromPreviousRealPoint = 0;
                                } else {
                                    $point->setElevation();
                                    $distanceAddedFromPreviousRealPoint += $distance;
                                }
                            } else {
                                $elevationChange = $previousElevation - $point->getElevation();
                                if ($this->isPointReal($distance + $distanceAddedFromPreviousRealPoint, $elevationChange)) {
                                    $negativeElevation += $elevationChange;
                                    $distanceAddedFromPreviousRealPoint = 0;
                                } else {
                                    $point->setElevation();
                                    $distanceAddedFromPreviousRealPoint += $distance;
                                }
                            }
                        }
                    }

                    $version->addPoint($point);

                    $order++;

                    $previousPoint = $point;
                    if ($point->getElevation()) {
                        $previousElevation = $point->getElevation();
                    }
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
     * @param iterable $labels
     *
     * @return array
     */
    public function generateElevationData(iterable $pointCollection, iterable $labels): array
    {
        $return = [];

        foreach ($pointCollection as $item) {
            reset($item);
            $return[] = [];
        }

        $collectionsCount = count($pointCollection);

        foreach ($labels as $labelIndex => $labelDistance) {
            $lastKnownElevation = null;
            for ($q = 0; $q < $collectionsCount; $q++) {
                $currentPoint = current($pointCollection[$q]);
                if ($currentPoint->getElevation()) {
                    $lastKnownElevation = $currentPoint->getElevation();
                }

                // case: skip point
                while ($currentPoint && $currentPoint->getDistance() < $labelDistance) {
                    $currentPoint = next($pointCollection[$q]);
                    if ($currentPoint->getElevation()) {
                        $lastKnownElevation = $currentPoint->getElevation();
                    }
                }

                if ($currentPoint === false) {
                    continue;
                }

                // case: skip label
                if (isset($labels[$labelIndex + 1])) {
                    $nextLabelDistance = $labels[$labelIndex + 1];
                    if ($nextLabelDistance < $currentPoint->getDistance()) {
                        $return[$q][] = $lastKnownElevation;
                        continue;
                    }
                }


                $return[$q][] = $this->getPointElevation($currentPoint, $pointCollection[$q], $lastKnownElevation);
                next($pointCollection[$q]);
            }
        }

        return $return;
    }

    /**
     * Return point elevation.
     * If there is no elevation data then use siblings to generate the elevation
     */
    public function getPointElevation(Point $point, iterable $pointCollection, $defaultElevation = 0): ?float
    {
        if ($point->getElevation()) {
            return $point->getElevation();
        }

        reset($pointCollection);
        while ($point !== current($pointCollection)) {
            next($pointCollection);
        }
        next($pointCollection);

        while ($previousPoint = prev($pointCollection)) {
            if ($previousPoint->getElevation()) {
                return $previousPoint->getElevation();
            }
        }

        while ($nextPoint = next($pointCollection)) {
            if ($nextPoint->getElevation()) {
                return $nextPoint->getElevation();
            }
        }

        return $defaultElevation;
    }
}
