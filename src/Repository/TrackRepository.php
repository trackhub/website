<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TrackRepository extends EntityRepository
{
    /**
     * Apply filter to return only public tracks
     */
    public function andWhereTrackIsPublic(QueryBuilder $qb): self
    {
        $qb->andWhere(
            $qb->expr()->eq($qb->getRootAliases()[0] . '.visibility', Track::VISIBILITY_PUBLIC)
        );

        return $this;
    }

    /**
     * Filter tracks by coordinates
     */
    public function andWhereInCoordinates(QueryBuilder $qb, array $skipTracks, float $neLat, float $swLat, float $neLon, float $swLon): self
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

        if (!empty($skipTracks)) {
            $qb->andWhere(
                $qb->expr()->notIn($alias . '.id', $skipTracks)
            );
        }

        return $this;
    }

    /**
     * Used in the index page
     */
    public function findLatestTrackTypes(): array
    {
        $data = [];

        foreach (Track::VALID_TYPES as $type) {
            $qb = $this->createQueryBuilder('t');

            $this->andWhereTrackIsPublic($qb);

            $qb->andWhere(
                $qb->expr()->eq($qb->getRootAliases()[0] . '.type', $type)
            );

            $data[$type] = $qb
                ->orderBy('t.createdAt', 'desc')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }

        return $data;
    }

    public function findByIdOrSlug(string $name): ?Track
    {
        $byId = $this->findOneBy(['id' => $name]);
        if ($byId) {
            return $byId;
        }

        $slugRepo = $this->getEntityManager()->getRepository(Track\Slug::class);
        $bySlug = $slugRepo->findOneBy(['slug' => $name]);

        if ($bySlug) {
            return $bySlug->getTrack();
        }

        return null;
    }
}
