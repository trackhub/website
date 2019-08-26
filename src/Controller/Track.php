<?php

namespace App\Controller;

use App\Entity\File\TrackFile;
use App\Entity\Track\Image;
use App\Entity\Track\Version;
use App\Entity\Video\Youtube;
use App\Form\Type\TrackVersion;
use App\Repository\TrackRepository;
use App\Track\Exporter;
use App\Track\Processor;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Tekstove\UrlVideoParser\Exception\ParseException;
use Tekstove\UrlVideoParser\Youtube\YoutubeParser;

class Track extends AbstractController
{
    public function new(Request $request, LoggerInterface $logger)
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
            $processor = new Processor();
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

            $track->addOptimizedPoints($optimizedPoints);
            $track->addVersion($trackVersion);
            $track->setType($form->get('type')->getData());
            $track->setName($form->get('name')->getData());
            $track->setVisibility($form->get('visibility')->getData());

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

                return $this->redirectToRoute('gps-view', ['id' => $track->getId()]);
            }
        }

        return $this->render(
            'gps/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function edit(Request $request, $id, LoggerInterface $logger)
    {
        $track = $this->getDoctrine()->getRepository(\App\Entity\Track::class)->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('edit', $track);

        $form = $this->createForm(\App\Form\Type\Track::class);
        $form->get('name')->setData($track->getName());
        $form->get('type')->setData($track->getType());
        $form->get('visibility')->setData($track->getVisibility());

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
            $track->setName($form->get('name')->getData());
            $track->setType($form->get('type')->getData());
            $track->setVisibility($form->get('visibility')->getData());

            $this->getDoctrine()->getManager()
                ->flush();

            return $this->redirectToRoute('gps-view', ['id' => $track->getId()]);
        }

        return $this->render(
            'gps/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function newVersion(Request $request, string $id)
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
            $processor = new Processor();
            $trackVersion = new Version($this->getUser());
            $processor->process($fileContent, $trackVersion);

            $trackFile = new TrackFile($trackVersion, $fileContent);
            $trackVersion->setFile($trackFile);

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

    public function view($id, TrackRepository $repo)
    {
        $gps = $repo->findOneBy(['id' => $id]);

        $canonicalUrl = null;
        if (!$gps) {
            $canonicalUrl = $this->generateUrl('gps-view', ['id' => $id]);
            $gps = $repo->findOneBy(['slug' => $id]);
        }

        /** @var $gps \App\Entity\Track */

        if (!$gps) {
            throw new NotFoundHttpException("Track not found");
        }

        $processor = new Processor();

        $pointsCollection = [];

        foreach ($gps->getVersions() as $loopIndex => $version) {
            $pointsCollection[] = $version->getPoints()->toArray();
        }

        foreach ($gps->getDownhillVersions() as $loopIndex => $item) {
            $pointsCollection[] = $item->getPoints()->toArray();
        }

        foreach ($gps->getUphillVersions() as $loopIndex => $item) {
            $pointsCollection[] = $item->getPoints()->toArray();
        }

        $labels = $processor->generateElevationLables($pointsCollection, 150);

        $values = $processor->generateElevationData($pointsCollection, $labels);

        foreach ($labels as &$label) {
            $label = number_format($label, 0, '', ' ') . ' m';
        }
        unset($label);

        $dataSets = [];
        reset($values);
        for ($q = 0; $q < $gps->getVersions()->count(); $q++) {
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

        for ($q = 0; $q < count($gps->getDownhillVersions()); $q++) {
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

        for ($q = 0; $q < count($gps->getUphillVersions()); $q++) {
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

        $appTitle = $gps->getName();
        switch ($gps->getType()) {
            case \App\Entity\Track::TYPE_CYCLING:
                $appTitle .= ' mountain bike trail';
        }

        return $this->render(
            'gps/view.html.twig',
            [
                'track' => $gps,
                'elevationData' => $dataSets,
                'elevationLabels' => $labels,
                'app_canonical_url' => $canonicalUrl,
                'app_title' => $appTitle,
                'canEdit' => $this->isGranted('edit', $gps),
            ]
        );
    }

    public function download($id, TrackRepository $repo)
    {
        $track = $repo->findOneBy(['id' => $id]);

        $exporter = new Exporter();
        $exported = $exporter->export($track->getVersions(), Exporter::FORMAT_GPX);

        $response = new \Symfony\Component\HttpFoundation\Response(
            $exported,
            200,
            [
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="track.gpx";',
            ]
        );

        return $response;
    }

    public function downloadBatch(Request $request)
    {
        $versions = $request->request->get('versions');
        $versionRepo = $this->getDoctrine()->getRepository(Version::class);
        $versionsCollection = $versionRepo->findBy(['id' => $versions]);

        $exporter = new Exporter();
        $exported = $exporter->export($versionsCollection, Exporter::FORMAT_GPX);

        $response = new \Symfony\Component\HttpFoundation\Response(
            $exported,
            200,
            [
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="track.gpx";',
            ]
        );

        return $response;
    }

    public function addImage(string $id, Request $request, TrackRepository $trackRepo, TranslatorInterface $translator)
    {
        $track = $trackRepo->findOneBy(['id' => $id]);

        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('file_upload', $token)) {
            throw $this->createAccessDeniedException('csrf check failed');
        }

        $file = $request->files->get('file');
        /* @var $file UploadedFile */
        if (!$file->isValid()) {
            return new Response(
                json_encode([
                    'status' => 1,
                    'error' => $translator->trans('Upload failed'),
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'text/json']
            );
        }

        $extension = $file->getClientOriginalExtension();
        $extension = mb_strtolower($extension);

        if (!in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
            return new Response(
                json_encode([
                    'status' => 1,
                    'error' => $translator->trans('Image format is not allowed'),
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'text/json']
            );
        }

        $uploadDirectory = $this->getParameter('track_images_directory') . DIRECTORY_SEPARATOR;
        $sqlFilepath = $track->getCreatedAt()->format('Y') . DIRECTORY_SEPARATOR . $track->getId();
        $uploadDirectory .= $sqlFilepath;

        $uploadFilename = uniqid() . '.' . $extension;
        $sqlFilepath .= DIRECTORY_SEPARATOR . $uploadFilename;

        $file->move(
            $uploadDirectory,
            $uploadFilename
        );

        $image = new Image(
            $sqlFilepath,
            $this->getUser(),
            $track
        );

        $this->getDoctrine()->getManager()->persist($image);

        $this->getDoctrine()->getManager()->flush();

        return new Response(
            json_encode(['status' => 0]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/json']
        );
    }

    public function generateThumbnail(int $year, string $trackId, $imagePath)
    {
        $originalImagePath = $this->getParameter('track_images_directory') . DIRECTORY_SEPARATOR;
        $originalImagePath .= $year . DIRECTORY_SEPARATOR . $trackId . DIRECTORY_SEPARATOR . $imagePath;

        $image = new \Imagick($originalImagePath);
        $image->adaptiveResizeImage(400,400);

        $thumbnailPathDir = $this->getParameter('track_images_thumbnails_directory');
        $thumbnailPathDir .= DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $trackId;
        $thumbnailPath = $thumbnailPathDir . DIRECTORY_SEPARATOR . $imagePath;

        $fs = new Filesystem();

        $fs->mkdir($thumbnailPathDir);

        file_put_contents(
            $thumbnailPath,
            $image
        );


    }
}
