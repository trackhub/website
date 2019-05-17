<?php

namespace App\Controller;

use App\Entity\Track\Point;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Home extends AbstractController
{
    public function home()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $qb = $entityManager->createQueryBuilder();
        /* @var $qb QueryBuilder*/
        $qb->select('t');
        $qb->from(\App\Entity\Track::class, 't');
        $qb->orderBy('t.createdAt', 'desc');
        $qb->setMaxResults(10);

        $latestTracks = $qb->getQuery()->getResult();

        return $this->render(
            'home/home.html.twig',
            [
                'latestTracks' => $latestTracks,
            ]
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
        $qb->select('count(g.id)');

        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte('g.pointNorthEastLat', $neLat),
                $qb->expr()->gte('g.pointSouthWestLat', $swLat),
                $qb->expr()->lte('g.pointNorthEastLng', $neLon),
                $qb->expr()->gte('g.pointSouthWestLng', $swLon)
            )
        );

        $qb->andWhere(
            $qb->expr()->notIn('g.id', $skipTracksAsArray)
        );

        $q = $qb->getQuery();
        $data = $q->getSingleResult();
        $count = current($data);

        if ($count > 10) {
            // status 2 = too many tracks found
            return new Response(
                json_encode([
                    'status' => 2,
                ]),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/json',
                ]
            );
        } else {
            $qb = $em->createQueryBuilder('g');
            $qb->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('g.pointNorthEastLat', $neLat),
                    $qb->expr()->gte('g.pointSouthWestLat', $swLat),
                    $qb->expr()->lte('g.pointNorthEastLng', $neLon),
                    $qb->expr()->gte('g.pointSouthWestLng', $swLon)
                )
            );
            $qb->andWhere(
                $qb->expr()->notIn('g.id', $skipTracksAsArray)
            );


            $qb->select('g');
            $q = $qb->getQuery();
            $qResult = $q->getResult();
            /* @var $qResult \App\Entity\Track[] */

            $responseData = [];

            foreach($qResult as $gps) {
                $gpsArrayData = [];
                $gpsArrayData['id'] = $gps->getId();
                $gpsArrayData['name'] = $gps->getName();
                foreach ($gps->getOptimizedPoints() as $point) {
                    /* @var $point Point */
                    $gpsArrayData['points'][] = [
                        'lat' => $point->getLat(),
                        'lng' => $point->getLng(),
                    ];
                }

                $responseData[] = $gpsArrayData;
            }
        }

        return new Response(
            json_encode([
                'status' => 1,
                'data' => $responseData,
            ]),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/json',
            ]
        );
    }
}
