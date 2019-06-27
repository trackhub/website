<?php

namespace App\Controller;

use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Point;
use App\Repository\TrackRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Home extends AbstractController
{
    public function home()
    {
        $data = [];

        /* @var $repo TrackRepository */
        $repo = $this
            ->getDoctrine()
            ->getRepository(\App\Entity\Track::class);

        foreach (\App\Entity\Track::VALID_TYPES as $type) {
            $qb = $repo->createQueryBuilder('t');

            $repo
                ->filterAccess($qb)
                ->filterType($qb, $type);

            $data[$type] = $qb
                ->orderBy('t.createdAt', 'desc')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }

        /* TODO: keys may not exists in $data */

        return $this->render(
            'home/home.html.twig',
            [
                'latestTracks' => $data[\App\Entity\Track::TYPE_CYCLING],
                'latestTracksHike' => $data[\App\Entity\Track::TYPE_HIKING],
            ]
        );
    }

    public function find($neLat, $neLon, $swLat, $swLon, Request $request)
    {
        /* FIXME: Make this work */
//        $skipTracks = $request->request->get('skipTracks', []);
//        $skipTracksAsArray = explode(',', $skipTracks);
        $skipTracksAsArray = [];

        /* @var $repo TrackRepository */
        $repo = $this
            ->getDoctrine()
            ->getRepository(\App\Entity\Track::class);

        $qb = $repo->createQueryBuilder('g');

        $repo
            ->filterAccess($qb)
            ->filterSearch($qb, $skipTracksAsArray, $neLat, $swLat, $neLon, $swLon);

        $data = $qb
            ->select($qb->expr()->count('g.id'))
            ->getQuery()
            ->getSingleResult();

        $count = current($data);

        if ($count > 10) {
            $status = 2; // 2 = too many tracks
        } else {
            $status = 1; // 1 = ok
        }

        $qb = $repo->createQueryBuilder('g');

        /* @var $qResult \App\Entity\Track[] */
        $repo
            ->filterAccess($qb)
            ->filterSearch($qb, $skipTracksAsArray, $neLat, $swLat, $neLon, $swLon);

        $qResult = $qb
            ->select('g')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $responseData = [];

        foreach ($qResult as $gps) {
            $gpsArrayData = [];
            $gpsArrayData['id'] = $gps->getId();
            $gpsArrayData['name'] = $gps->getName();
            $gpsArrayData['slugOrId'] = $gps->getSlugOrId();
            $gpsArrayData['type'] = $gps->getType();

            /* @var $point OptimizedPoint */
            foreach ($gps->getOptimizedPoints() as $point) {
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
