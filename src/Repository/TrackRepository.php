<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;

class TrackRepository extends EntityRepository
{
    public function getAllRelatedDownhillVersions(Track $track, $useCache = false)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->join('t.downhills', 'dh');
        $qb->where('dh = :dh');
        $qb->setParameter(':dh', $track);

        $result = $qb->getQuery()->execute();

        $versions = [$track->getVersions()];

        $versions = $track->getDownhillVersions($useCache, $versions);

        foreach ($result as $relatedDh) {
            /* @var $relatedDh Track */
            foreach ($relatedDh->getVersions() as $version) {
                if (array_search($version, $versions) === false) {
                    $versions[] = $version;
                }
            }


            $versions = array_merge(
                $versions,
                $relatedDh->getDownhillVersions($useCache, $versions)
            );
        }

        // remove given track versions
        foreach ($track->getVersions() as $version) {
            array_pop($versions);
        }

        return $versions;
    }
}
