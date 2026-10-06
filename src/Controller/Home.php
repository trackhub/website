<?php

namespace App\Controller;

use App\Repository\Track\ImageRepository;
use App\Repository\Place\ImageRepository as PlaceImageRepo;
use App\Repository\TrackRepository;
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
}
