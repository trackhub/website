<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;

class TrackRepository extends EntityRepository
{
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
