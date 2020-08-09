<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class PlaceRepository extends EntityRepository
{
    public function nearTrack(QueryBuilder $qb, Track $track, float $allowedTolerance = 0)
    {
        $this->andWhereInCoordinates(
            $qb,
            [],
            $track->getPointNorthEastLat() + $allowedTolerance,
            $track->getPointSouthWestLat() - $allowedTolerance,
            $track->getPointNorthEastLng() + $allowedTolerance,
            $track->getPointSouthWestLng() - $allowedTolerance,
        );
    }

    /**
     * Filter points by coordinates
     */
    public function andWhereInCoordinates(QueryBuilder $qb, array $skipPoints, float $neLat, float $swLat, float $neLon, float $swLon): self
    {
        $alias = $qb->getRootAliases()[0];

        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte($alias . '.lat', $neLat),
                $qb->expr()->gte($alias . '.lat', $swLat),
                $qb->expr()->lte($alias . '.lng', $neLon),
                $qb->expr()->gte($alias . '.lng', $swLon)
            )
        );

        if (!empty($skipPoints)) {
            $qb->andWhere(
                $qb->expr()->notIn($alias . '.id', $skipPoints)
            );
        }

        return $this;
    }
}
