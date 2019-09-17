<?php

namespace App\Repository\Track;

use Doctrine\ORM\EntityRepository;

class ImageRepository extends EntityRepository
{
    public function getLatestImages(int $limit = 10)
    {
        $qb = $this->createQueryBuilder('i');
        $qb->setMaxResults($limit);
        $qb->addOrderBy('i.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
