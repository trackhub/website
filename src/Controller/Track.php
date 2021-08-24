<?php

namespace App\Controller;

use App\Entity\File\TrackFile;
use App\Entity\Track\Slug;
use App\Entity\Track\VersionRating;
use App\Entity\Track\Version;
use App\Entity\Video\Youtube;
use App\Form\Type\TrackVersion;
use App\Repository\PlaceRepository;
use App\Repository\Track\SlugRepository;
use App\Repository\TrackRepository;
use App\Track\ElevationDataGenerator;
use App\Track\Exporter;
use App\Track\Processor;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tekstove\UrlVideoParser\Exception\ParseException;
use Tekstove\UrlVideoParser\Youtube\YoutubeParser;

class Track extends AbstractController
{
    /**
     * 1 degree is ~111km
     * 0.005 ~ 0.5km
     * 0.01 ~ 1km
     * 0.02 ~ 2km
     */
    protected const ALLOWED_PLACE_NEAR_TRACK_TOLERANCE = 0.02;

    public function new(Request $request, LoggerInterface $logger, SlugRepository $slugRepo, Processor $processor)
    {
        $form = $this->createForm(\App\Form\Type\Track::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formIsValid = true;
            $file = $form->get('file');
            $fileData = $file->getData();
            /* @var $fileData UploadedFile */
            $fileContent = file_get_contents($fileData->getRealPath());
            $track = new \App\Entity\Track($this->getUser());
            // we should have service for gpx processing
            $trackVersion = new Version($this->getUser());
            try {
                $processor->process($fileContent, $trackVersion);
            } catch (\Exception $e) {
                $formIsValid = false;
                $logger->error("Track file parsing error", ['content' => $fileContent, 'e' => $e]);
                $form->get('file')->addError(
                    new FormError('cannot parse the file')
                );
            }

            $optimizedPoints = $processor->generateOptimizedPoints($trackVersion);

            $trackVersion->setDifficulty($form->get('difficulty')->getData());

            $track->addOptimizedPoints($optimizedPoints);
            $track->addVersion($trackVersion);
            $track->setType($form->get('type')->getData());

            $track->setNameEn($form->get('nameEn')->getData());
            $track->setNameBg($form->get('nameBg')->getData());
            $track->setVisibility($form->get('visibility')->getData());
            $track->setDescriptionBg($form->get('descriptionBg')->getData());
            $track->setDescriptionEn($form->get('descriptionEn')->getData());

            $videoParser = new YoutubeParser();
            $youtubeVideos = [];
            foreach ($form->get('videosYoutube')->getData() as $youtubeLink) {
                if (empty($youtubeLink['link'])) {
                    continue;
                }

                try {
                    $videoId = $videoParser->getId($youtubeLink['link']);
                    $youtubeVideos[] = new Youtube($videoId);
                } catch (ParseException $e) {
                    $logger->error("Video parsing failed", [$e->getMessage()]);
                }
            }

            $track->setvideosYoutube($youtubeVideos);

            if (!$form->get('slug')->isEmpty()) {
                $slug = $form->get('slug')->getData();
                $track->setSlug($slug);
                $existingSlug = $slugRepo->findOneBy(['slug' => $slug]);
                if ($existingSlug) {
                    $form->get('slug')->addError(
                        new FormError('Slug is already taken')
                    );
                }

                $slugEntity = new Slug($track, $slug);
                $this->getDoctrine()->getManager()
                    ->persist($slugEntity);
            }

            $processor->postProcess($track);

            if ($track->getOptimizedPoints()->isEmpty()) {
                $formIsValid = false;
                $form->get('file')->addError(
                    new FormError('error') // @FIXME translate and add specific error
                );
            }

            if ($formIsValid) {
                $trackFile = new TrackFile($trackVersion, $fileContent);
                $trackVersion->setFile($trackFile);

                $this->getDoctrine()->getManager()
                    ->persist($track);
                $this->getDoctrine()->getManager()
                    ->flush();

                return $this->redirectToRoute('gps-view', ['id' => $track->getSlugOrId()]);
            }
        }

        return $this->render(
            'gps/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function edit(
        $id,
        Request $request,
        LoggerInterface $logger,
        SlugRepository $slugRepo,
        TrackRepository $trackRepo,
        EntityManagerInterface $em
    ) {
        $track = $trackRepo->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('edit', $track);

        $form = $this->createForm(\App\Form\Type\Track::class);
        $form->get('nameEn')->setData($track->getNameEn());
        $form->get('nameBg')->setData($track->getNameBg());
        $form->get('type')->setData($track->getType());
        $form->get('visibility')->setData($track->getVisibility());
        $form->get('slug')->setData($track->getSlug());
        $form->get('descriptionBg')->setData($track->getDescriptionBg());
        $form->get('descriptionEn')->setData($track->getDescriptionEn());

        $youtubeFormData = [];
        foreach ($track->getVideosYoutube() as $youtube) {
            $youtubeFormData[] = [
                'link' => $youtube->getLink(),
            ];
        }
        $form->get('videosYoutube')->setData($youtubeFormData);

        $form->add('submit', SubmitType::class);
        $form->remove('file');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formIsValid = true;
            $videoParser = new YoutubeParser();
            $youtubeVideos = [];
            foreach ($form->get('videosYoutube')->getData() as $youtubeLink) {
                if (empty($youtubeLink['link'])) {
                    continue;
                }

                try {
                    $videoId = $videoParser->getId($youtubeLink['link']);
                    $youtubeVideos[] = new Youtube($videoId);
                } catch (ParseException $e) {
                    // if we have video id
                    if (preg_match('~[a-zA-Z0-9\-_]{5,}~', $youtubeLink['link'])) {
                        $youtubeVideos[] = new Youtube($youtubeLink['link']);
                    } else {
                        $logger->error("Video parsing failed", [$e->getMessage()]);
                    }
                }
            }

            $track->setvideosYoutube($youtubeVideos);
            $track->setNameEn($form->get('nameEn')->getData());
            $track->setNameBg($form->get('nameBg')->getData());
            $track->setType($form->get('type')->getData());
            $track->setVisibility($form->get('visibility')->getData());
            $track->setDescriptionBg($form->get('descriptionBg')->getData());
            $track->setDescriptionEn($form->get('descriptionEn')->getData());

            if (!$form->get('slug')->isEmpty()) {
                $slug = $form->get('slug')->getData();

                $addNewSlug = false;
                if ($track->getSlug() !== $slug) {
                    $track->setSlug($slug);
                    $existingSlug = $slugRepo->findOneBy(['slug' => $slug]);
                    if ($existingSlug) {
                        if ($existingSlug->getTrack() !== $track) {
                            $formIsValid = false;
                            $form->get('slug')->addError(
                                new FormError('Slug is already taken')
                            );
                        }
                    } else {
                        $addNewSlug = true;
                    }
                }

                if ($addNewSlug) {
                    $slugEntity = new Slug($track, $slug);
                    $this->getDoctrine()->getManager()
                        ->persist($slugEntity);
                }
            }

            if ($formIsValid) {
                $em->flush();

                return $this->redirectToRoute('gps-view', ['id' => $track->getSlugOrId()]);
            }
        }

        return $this->render(
            'gps/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function newVersion(Request $request, Processor $processor, string $id)
    {
        $track = $this->getDoctrine()->getRepository(\App\Entity\Track::class)->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('edit', $track);

        $form = $this->createForm(TrackVersion::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file');
            $fileData = $file->getData();
            /* @var $fileData UploadedFile */
            $fileContent = file_get_contents($fileData->getRealPath());

            /* @FIXME
            - only gps!
             */

            // we should have service for gpx processing
            $trackVersion = new Version($this->getUser());
            $processor->process($fileContent, $trackVersion);

            $trackFile = new TrackFile($trackVersion, $fileContent);
            $trackVersion->setFile($trackFile);
            $trackVersion->setDifficulty($form->get('difficulty')->getData());

            $track->addVersion($trackVersion);

            if ($trackVersion->getPoints()->isEmpty()) {
                $form->get('file')->addError(
                    new FormError('error') // @FIXME translate and add specific error
                );
            } else {
                $this->getDoctrine()->getManager()
                    ->persist($track);
                $this->getDoctrine()->getManager()
                    ->flush();

                return $this->redirectToRoute('gps-view', ['id' => $track->getId()]);
            }
        }

        return $this->render(
            'gps/newVersion.html.twig',
            [
                'track' => $track,
                'form' => $form->createView(),
            ]
        );
    }

    public function rate(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->getDoctrine()
            ->getManager();

        /**
         * @var $user \App\Entity\User\User
         */
        $user = $this->getUser();

        /**
         * @var $version Version
         */
        $version = $em->getRepository(\App\Entity\Track\Version::class)
            ->findOneBy(['id' => $id]);
        if (is_null($version)) {
            return new JsonResponse(
                [
                    'message' => "Invalid track version"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($request->getRealMethod() === 'POST') {
            $ratingRepo = $em->getRepository(VersionRating::class);
            /*
             * Check if user has already submitted rating
             */
            $rating = $ratingRepo->findOneBy([
                'version' => $id,
                'user' => $user,
            ]);

            /*
             * If $rating is null, create new row
             */
            if (is_null($rating)) {
                $rating = new VersionRating();
                $rating->setUser($user);
                $rating->setVersion($version);
            }

            /**
             * Check if rating is within range of 1 to 5
             */
            $newRating = $request->request->get('rating');
            if ($newRating < 1 || $newRating > 5) {
                return new JsonResponse(
                    [
                        'message' => 'Invalid rating: ' . $newRating
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $rating->setRating($newRating);
            $em->persist($rating);
            $em->flush();
        }

        $average = 0;
        /**
         * @var $r VersionRating
         */
        foreach ($version->getRatings() as $r) {
            $average += $r->getRating();
        }
        $average = $average / $version->getVotes();

        return new JsonResponse([
            'rating' => round($average, 2),
            'votes' => $version->getVotes(),
        ]);
    }

    public function view($id, Request $request, TrackRepository $repo, PlaceRepository $placeRepo, ElevationDataGenerator $elevationGenerator)
    {
        $track = $repo->findByIdOrSlug($id);

        if (!$track) {
            throw new NotFoundHttpException("Track not found");
        }

        $canonicalUrl = null;
        if ($track->getSlug()) {
            $canonicalUrl = $this->generateUrl(
                'gps-view',
                ['id' => $track->getSlug()],
            );
        }

        $placesQueryBuilder = $placeRepo->createQueryBuilder('p');
        $placeRepo->nearTrack($placesQueryBuilder, $track, self::ALLOWED_PLACE_NEAR_TRACK_TOLERANCE);

        $places = $placesQueryBuilder->getQuery()->getResult();

        $pointsCollection = [];

        foreach ($track->getVersions() as $loopIndex => $version) {
            $pointsCollection[] = $version->getPoints()->toArray();
        }

        foreach ($track->getDownhillVersions() as $loopIndex => $item) {
            $pointsCollection[] = $item->getPoints()->toArray();
        }

        foreach ($track->getUphillVersions() as $loopIndex => $item) {
            $pointsCollection[] = $item->getPoints()->toArray();
        }

        $labels = $elevationGenerator->generateElevationLables($pointsCollection, 150);

        $values = $elevationGenerator->generateElevationData($pointsCollection, $labels);

        foreach ($labels as &$label) {
            $label = number_format($label, 0, '', ' ') . ' m';
        }
        unset($label);

        $dataSets = [];
        reset($values);
        for ($q = 0; $q < $track->getVersions()->count(); $q++) {
            $currentValues = current($values);
            foreach ($currentValues as &$value) {
                $value = (int) $value;
            }
            unset($value);

            $dataSets[] = [
                'data' => $currentValues,
                'label' => 'main track #' . ($q + 1),
                'borderColor' => 'red',
            ];

            next($values);
        }

        for ($q = 0; $q < count($track->getDownhillVersions()); $q++) {
            $currentValues = current($values);
            foreach ($currentValues as &$value) {
                $value = (int) $value;
            }
            unset($value);

            $dataSets[] = [
                'data' => $currentValues,
                'label' => 'downhill version #' . ($q + 1),
                'borderColor' => 'orange',
            ];

            next($values);
        }

        for ($q = 0; $q < count($track->getUphillVersions()); $q++) {
            $currentValues = current($values);
            foreach ($currentValues as &$value) {
                $value = (int) $value;
            }
            unset($value);

            $dataSets[] = [
                'data' => $currentValues,
                'label' => 'uphill version #' . ($q + 1),
                'borderColor' => 'green',
            ];

            next($values);
        }

        $appTitle = $track->getName($request->getLocale());

        return $this->render(
            'gps/view.html.twig',
            [
                'track' => $track,
                'elevationData' => $dataSets,
                'elevationLabels' => $labels,
                'places' => $places,
                'app_canonical_url' => $canonicalUrl,
                'app_title' => $appTitle,
                'canEdit' => $this->isGranted('edit', $track),
            ]
        );
    }

    public function download($id, TrackRepository $repo, PlaceRepository $placeRepo)
    {
        $track = $repo->findOneBy(['id' => $id]);

        $exporter = new Exporter($track->getVersions());

        $placesQueryBuilder = $placeRepo->createQueryBuilder('p');
        $placeRepo->nearTrack($placesQueryBuilder, $track, self::ALLOWED_PLACE_NEAR_TRACK_TOLERANCE);

        $places = $placesQueryBuilder->getQuery()->getResult();

        $exporter->addPlaces($places);
        $exported = $exporter->export(Exporter::FORMAT_GPX);

        $response = new \Symfony\Component\HttpFoundation\Response(
            $exported,
            200,
            [
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="track.gpx";',
                'Content-Type' => 'application/octet-stream',
            ]
        );

        return $response;
    }

    public function downloadBatch(Request $request, PlaceRepository $placeRepo)
    {
        $versions = $request->request->get('versions');
        $versionRepo = $this->getDoctrine()->getRepository(Version::class);
        $versionsCollection = $versionRepo->findBy(['id' => $versions]);

        $exporter = new Exporter($versionsCollection);

        if (!empty($versionsCollection)) {
            $track = $versionsCollection[0]->getTrack();
            $placesQueryBuilder = $placeRepo->createQueryBuilder('p');
            $placeRepo->nearTrack($placesQueryBuilder, $track, self::ALLOWED_PLACE_NEAR_TRACK_TOLERANCE);

            $places = $placesQueryBuilder->getQuery()->getResult();

            $exporter->addPlaces($places);
        }

        $exported = $exporter->export(Exporter::FORMAT_GPX);

        return new \Symfony\Component\HttpFoundation\Response(
            $exported,
            200,
            [
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="track.gpx";',
                'Content-Type' => 'application/octet-stream',
            ]
        );
    }
}
