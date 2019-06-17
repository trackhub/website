<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TrackRepository extends EntityRepository
{
    public function filterAccess(QueryBuilder $qb, string $trackAlias = 't')
    {
        $qb->andWhere(
            $qb->expr()->eq($trackAlias . '.visibility', Track::VISIBILITY_PUBLIC)
        );
    }
}
