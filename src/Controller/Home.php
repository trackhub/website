<?php

namespace App\Controller;

use App\Entity\Track\OptimizedPoint;
use App\Repository\Track\ImageRepository;
use App\Repository\TrackRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Track;

class Home extends AbstractController
{
    public function index()
    {
        return $this->redirectToRoute('home');
    }

    public function home(TrackRepository $repo, ImageRepository $imageRepository)
    {
        $trackData = $repo->findLatestTrackTypes();
        $images = $imageRepository->getLatestImages(5);

        return $this->render(
            'home/home.html.twig',
            [
                'latestTracks' => $trackData[Track::TYPE_CYCLING],
                'latestTracksHike' => $trackData[Track::TYPE_HIKING],
                'latestImages' => $images,
            ]
        );
    }

    public function find($neLat, $neLon, $swLat, $swLon, Request $request, TrackRepository $repo)
    {
        $skipTracks = $request->request->get('skipTracks', []);
        $skipTracksAsArray = explode(',', $skipTracks);

        $qb = $repo->createQueryBuilder('g');

        $repo->andWhereTrackIsPublic($qb)
             ->andWhereInCoordinates($qb, $skipTracksAsArray, $neLat, $swLat, $neLon, $swLon);

        $data = $qb->select($qb->expr()->count('g.id'))
                   ->getQuery()
                   ->getSingleResult();

        $count = current($data);

        if ($count > 10) {
            $status = 2; // 2 = too many tracks
        } else {
            $status = 1; // 1 = ok
        }

        $qb = $repo->createQueryBuilder('g');

        $repo->andWhereTrackIsPublic($qb)
             ->andWhereInCoordinates($qb, $skipTracksAsArray, $neLat, $swLat, $neLon, $swLon);

        /* @var $qResult Track[] */
        $qResult = $qb
            ->select('g')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $responseData = [];

        foreach ($qResult as $gps) {
            $gpsArrayData = [];
            $gpsArrayData['id'] = $gps->getId();
            $gpsArrayData['name'] = $gps->getName($request->getLocale());
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
