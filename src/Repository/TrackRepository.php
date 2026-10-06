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
