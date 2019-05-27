<?php

namespace App\Controller;

use App\Entity\File\TrackFile;
use App\Entity\Track\Version;
use App\Form\Type\TrackVersion;
use App\Track\Exporter;
use App\Track\Processor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Track extends AbstractController
{
    public function new(Request $request)
    {
        $form = $this->createForm(\App\Form\Type\Track::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file');
            $fileData = $file->getData();
            /* @var $fileData UploadedFile */
            $c = file_get_contents($fileData->getRealPath());

            /* @FIXME
             - only gps!
             */


            $track = new \App\Entity\Track();
            // we should have service for gpx processing
            $processor = new Processor();
            $trackVersion = new Version($this->getUser());
            $processor->process($c, $trackVersion);

            $optimizedPoints = $processor->generateOptimizedPoints($trackVersion);

            $track->addOptimizedPoints($optimizedPoints);
            $track->addVersion($trackVersion);
            $track->setType($form->get('type')->getData());
            $track->setName($form->get('name')->getData());

            $processor->postProcess($track);

            if ($track->getOptimizedPoints()->isEmpty()) {
                $form->get('file')->addError(
                    new FormError('error') // @FIXME translate and add specific error
                );
            } else {
                $trackFile = new TrackFile($trackVersion, $c);
                $trackVersion->setFile($trackFile);


                $this->getDoctrine()->getManager()
                    ->persist($track);
                $this->getDoctrine()->getManager()
                    ->flush();

                return $this->redirectToRoute('home');
            }
        }

        return $this->render(
            'gps/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function edit(Request $request, $id)
    {
        throw new \Exception("Not implemented");

        $track = $this->getDoctrine()->getRepository(\App\Entity\Track::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(\App\Form\Type\Track::class, $track);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

    public function view($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
                ->getRepository(\App\Entity\Track::class);

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
        $elevationDataCollection = [];

        foreach ($gps->getVersions() as $loopIndex => $version) {
            $elevationDataCollection[] = [
                'values' => $processor->generateElevationData(
                    $version->getPoints()
                ),
                'label' => 'main track #' . ($loopIndex + 1),
                'borderColor' => 'red',
            ];
        }

        foreach ($gps->getDownhillVersions() as $loopIndex => $item) {
            $elevationDataCollection[] = [
                'values' => $processor->generateElevationData(
                    $item->getPoints()
                ),
                'label' => 'downhill track #' . ($loopIndex + 1),
                'borderColor' => 'orange',
            ];
        }

        foreach ($gps->getUphillVersions() as $loopIndex => $item) {
            $elevationDataCollection[] = [
                'values' => $processor->generateElevationData(
                    $item->getPoints()
                ),
                'label' => 'uphill track #' . ($loopIndex + 1),
                'borderColor' => 'green',
            ];
        }


        $elevationLabels = [];
        $elevationLabelsPoints = [];

        foreach ($elevationDataCollection as $versionIndex => $elevationItem) {
            $tmpElevationValues = [];
            $lastPointIndex = 0;
            foreach ($elevationItem['values'] as $elevationValueIndex => $elevationValueData) {
                if ($versionIndex === 0) {
                    $elevationLabels[] = $elevationValueData['label'] . ' km';
                    $elevationLabelsPoints[] = $elevationValueData['point'];
                }

                if ($versionIndex === 0) {
                    $tmpElevationValues[] = $elevationValueData['elev'];
                } else {
                    if (count($elevationLabels) > count($tmpElevationValues)) {
                        $currentLabelDistance = $elevationLabelsPoints[$lastPointIndex]->getDistance();
                        $currentElevationDataDistance = $elevationValueData['point']->getDistance();

                        $addPoint = true;
                        if (isset($elevationLabelsPoints[$lastPointIndex + 1])) {
                            $nextPoint = $elevationLabelsPoints[$lastPointIndex + 1];

                            if ($currentElevationDataDistance >= $nextPoint->getDistance()) {
                                $addPoint = false;
                            }
                        }

                        if ($addPoint && $currentElevationDataDistance >= $currentLabelDistance) {
                            $tmpElevationValues[] = $elevationValueData['elev'];
                            $lastPointIndex++;
                        }
                    }
                }
            }

            $dataSets[] = [
                'data' => $tmpElevationValues,
                'label' =>  $elevationItem['label'],
                'borderColor' => $elevationItem['borderColor'],
            ];
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
                'elevationLabels' => $elevationLabels,
                'app_canonical_url' => $canonicalUrl,
                'app_title' => $appTitle,
            ]
        );
    }

    public function download($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
                ->getRepository(\App\Entity\Track::class);
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
}
