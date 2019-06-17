<?php

namespace App\Controller;

use App\Entity\Track\Point;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Home extends AbstractController
{
    public function home()
    {
        $trackRepo = $this->getDoctrine()->getRepository(\App\Entity\Track::class);
        /* @var $trackRepo TrackRepository */
        $qbMtb = $trackRepo->createQueryBuilder('t');
        $trackRepo->filterAccess($qbMtb);
        $qbMtb->orderBy('t.createdAt', 'desc');
        $qbMtb->andWhere($qbMtb->expr()->eq('t.type', \App\Entity\Track::TYPE_CYCLING));
        $qbMtb->setMaxResults(10);

        $latestTracks = $qbMtb->getQuery()->getResult();

        $qbHike = $trackRepo->createQueryBuilder('t');
        $trackRepo->filterAccess($qbHike);
        $qbHike->andWhere($qbMtb->expr()->eq('t.type', \App\Entity\Track::TYPE_HIKING));
        $qbHike->orderBy('t.createdAt', 'desc');
        $qbHike->setMaxResults(10);

        $latestTracksHike = $qbHike->getQuery()->getResult();

        return $this->render(
            'home/home.html.twig',
            [
                'latestTracks' => $latestTracks,
                'latestTracksHike' => $latestTracksHike,
            ]
        );
    }

    private function applyTrackSearchFilters(QueryBuilder $qb, array $skipTracks, $neLat, $swLat, $neLon, $swLon)
    {
        $qb->andWhere($qb->expr()->eq('g.visibility', \App\Entity\Track::VISIBILITY_PUBLIC));
        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte('g.pointNorthEastLat', $neLat),
                $qb->expr()->gte('g.pointSouthWestLat', $swLat),
                $qb->expr()->lte('g.pointNorthEastLng', $neLon),
                $qb->expr()->gte('g.pointSouthWestLng', $swLon)
            )
        );

        $qb->andWhere(
            $qb->expr()->notIn('g.id', $skipTracks)
        );
    }

    public function find($neLat, $neLon, $swLat, $swLon, Request $request)
    {
        $skipTracks = $request->request->get('skipTracks', []);
        $skipTracksAsArray = explode(',', $skipTracks);

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getRepository(\App\Entity\Track::class);
        /* @var $em EntityRepository */

        $qb = $em->createQueryBuilder('g');
        $this->applyTrackSearchFilters($qb, $skipTracksAsArray, $neLat, $swLat, $neLat, $swLon);
        $qb->select('count(g.id)');

        $q = $qb->getQuery();
        $data = $q->getSingleResult();
        $count = current($data);

        if ($count > 10) {
            $status = 2; // 2 = too many tracks
        } else {
            $status = 1; // 1 = ok
        }

        $qb = $em->createQueryBuilder('g');
        $this->applyTrackSearchFilters($qb, $skipTracksAsArray, $neLat, $swLat, $neLat, $swLon);

        $qb->select('g');
        $qb->setMaxResults(10);
        $q = $qb->getQuery();
        $qResult = $q->getResult();
        /* @var $qResult \App\Entity\Track[] */

        $responseData = [];

        foreach ($qResult as $gps) {
            $gpsArrayData = [];
            $gpsArrayData['id'] = $gps->getId();
            $gpsArrayData['name'] = $gps->getName();
            $gpsArrayData['slugOrId'] = $gps->getSlugOrId();
            $gpsArrayData['type'] = $gps->getType();
            foreach ($gps->getOptimizedPoints() as $point) {
                /* @var $point Point */
                $gpsArrayData['points'][$point->getVersionIndex()][] = [
                    'lat' => $point->getLat(),
                    'lng' => $point->getLng(),
                ];
            }

            $responseData[] = $gpsArrayData;
        }

        return new Response(
            json_encode([
                'status' => $status,
                'data' => $responseData,
            ]),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/json',
            ]
        );
    }
}
