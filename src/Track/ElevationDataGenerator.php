<?php

namespace App\Track;

use App\Entity\Track\Point;

/**
 * Used in elevation visualization.
 * The main task is to generate missing elevations,
 * so we can generate better graphics
 */
class ElevationDataGenerator
{
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

                if ($currentPoint === false) {
                    continue;
                }

                if ($currentPoint->getElevation()) {
                    $lastKnownElevation = $currentPoint->getElevation();
                }

                // case: skip point
                while ($currentPoint && $currentPoint->getDistance() < $labelDistance) {
                    $currentPoint = next($pointCollection[$q]);
                    if ($currentPoint === false) {
                        break;
                    }

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
}
