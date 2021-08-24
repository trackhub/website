<?php

namespace App\Controller;

use App\Contract\Http\ApiResponseInterface;
use App\Entity\Track\OptimizedPoint;
use App\Place\ImageDetector;
use App\Repository\Track\ImageRepository;
use App\Repository\Place\ImageRepository as PlaceImageRepo;
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

    public function home(TrackRepository $repo, ImageRepository $imageRepository, PlaceImageRepo $placeImageRepo)
    {
        $trackData = $repo->findLatestTrackTypes();

        // average picture width is 300px
        // 1920 / 300 = 6.4
        // 3840 / 300 = 12.8
        $images = $imageRepository->getLatestImages(10);
        $placeImages = $placeImageRepo->getLatestImages(10);

        return $this->render(
            'home/home.html.twig',
            [
                'latestTracks' => $trackData[Track::TYPE_CYCLING],
                'latestTracksHike' => $trackData[Track::TYPE_HIKING],
                'latestImages' => $images,
                'latestPlaceImages' => $placeImages,
            ]
        );
    }

    public function find($neLat, $neLon, $swLat, $swLon, Request $request, TrackRepository $repo, ImageDetector $imageDetector)
    {
        $skipTracks = $request->request->get('skipTracks', '');
        $skipTracksAsArray = explode(',', $skipTracks);

        $tracks = $this->findTracks($neLat, $neLon, $swLat, $swLon, $repo, $skipTracksAsArray, $request->getLocale());

        $skipTracks = $request->request->get('skipPlaces', '');
        $skipPlacesAsArray = explode(',', $skipTracks);
        $places = $this->findPlaces($neLat, $neLon, $swLat, $swLon, $skipPlacesAsArray, $request->getLocale(), $imageDetector);

        return new Response(
            json_encode([
                'tracks' => [
                    'status' => $tracks['status'],
                    'data' => $tracks['data'],
                ],
                'places' => [
                    'status' => $places['status'],
                    'data' => $places['data'],
                ],
            ]),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/json',
            ]
        );
    }

    private function findTracks($neLat, $neLon, $swLat, $swLon, $repo, $skipTracks, $locale)
    {
        $qb = $repo->createQueryBuilder('g');

        $repo->andWhereTrackIsPublic($qb)
            ->andWhereInCoordinates($qb, $skipTracks, $neLat, $swLat, $neLon, $swLon);

        $data = $qb->select($qb->expr()->count('g.id'))
            ->getQuery()
            ->getSingleResult();

        $count = current($data);

        if ($count > 10) {
            $status = ApiResponseInterface::STATUS_TOO_MANY_ROWS_FOUND;
        } else {
            $status = ApiResponseInterface::STATUS_OK;
        }

        $qb = $repo->createQueryBuilder('g');

        $repo->andWhereTrackIsPublic($qb)
            ->andWhereInCoordinates($qb, $skipTracks, $neLat, $swLat, $neLon, $swLon);

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
            $gpsArrayData['name'] = $gps->getName($locale);
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

        return [
            'data' => $responseData,
            'status' => $status,
        ];
    }

    private function findPlaces($neLat, $neLon, $swLat, $swLon, $skipTracks, $locale, ImageDetector $imageDetector)
    {
        $repo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);

        $qb = $repo->createQueryBuilder('g');

        $repo->andWhereInCoordinates($qb, $skipTracks, $neLat, $swLat, $neLon, $swLon);

        $data = $qb->select($qb->expr()->count('g.id'))
            ->getQuery()
            ->getSingleResult();

        $count = current($data);

        if ($count > 10) {
            $status = ApiResponseInterface::STATUS_TOO_MANY_ROWS_FOUND;
        } else {
            $status = ApiResponseInterface::STATUS_OK;
        }

        $qb = $repo->createQueryBuilder('g');

        $repo->andWhereInCoordinates($qb, $skipTracks, $neLat, $swLat, $neLon, $swLon);

        /* @var $qResult \App\Entity\Place[] */
        $qResult = $qb
            ->select('g')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $responseData = [];

        foreach ($qResult as $gps) {
            $gpsArrayData = [];
            $gpsArrayData['id'] = $gps->getId();
            $gpsArrayData['slugOrId'] = $gps->getSlugOrId();
            $gpsArrayData['lat'] = $gps->getLat();
            $gpsArrayData['lng'] = $gps->getLng();
            $gpsArrayData['name'] = $gps->getName($locale);
            $gpsArrayData['icon'] = $imageDetector->getImage($gps->getType());
            $gpsArrayData['attraction'] = $gps->isAttraction();

            $responseData[] = $gpsArrayData;
        }

        return [
            'data' => $responseData,
            'status' => $status,
        ];
    }
}
