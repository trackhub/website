<?php


namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class LanguageRepository extends EntityRepository
{
    public function findAllNames()
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.name', 'ASC')
            ->getQuery()
            ->execute();
    }

}