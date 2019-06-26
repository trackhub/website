<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TrackRepository extends EntityRepository
{
    /**
     * Filter tracks based on visibility
     *
     * @param QueryBuilder $qb
     *
     * @return self
     */
    public function filterAccess(QueryBuilder $qb)
    {
        $qb->andWhere(
            $qb->expr()->eq($qb->getRootAliases()[0] . '.visibility', Track::VISIBILITY_PUBLIC)
        );

        return $this;

    }

    /**
     * Filter tracks based on type
     *
     * @param QueryBuilder $qb
     * @param int $type
     *
     * @return self
     */
    public function filterType(QueryBuilder $qb, int $type)
    {
        $qb->andWhere(
            $qb->expr()->eq($qb->getRootAliases()[0] . '.type', $type));

        return $this;

    }

    /**
     * Filter tracks by coordinates
     *
     * @param QueryBuilder $qb
     * @param array $skipTracks
     * @param $neLat
     * @param $swLat
     * @param $neLon
     * @param $swLon
     *
     * @return self
     */
    public function filterSearch(QueryBuilder $qb, array $skipTracks, $neLat, $swLat, $neLon, $swLon)
    {
        $alias = $qb->getRootAliases()[0];

        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte($alias . '.pointNorthEastLat', $neLat),
                $qb->expr()->gte($alias . '.pointSouthWestLat', $swLat),
                $qb->expr()->lte($alias . '.pointNorthEastLng', $neLon),
                $qb->expr()->gte($alias . '.pointSouthWestLng', $swLon)
            )
        );

        if (!empty($skipTracks))
            $qb->andWhere(
                $qb->expr()->notIn($alias . '.id', $skipTracks)
            );

        return $this;
    }
}
