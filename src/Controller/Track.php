<?php

namespace App\Controller;

use App\Entity\File\TrackFile;
use App\Entity\Track\Point;
use App\Entity\Track\Version;
use App\Form\Type\TrackVersion;
use App\Track\Processor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
            $trackVersion = new Version();
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
            $trackVersion = new Version();
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

                // @FIXME return response!
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
        /** @var $gps \App\Entity\Track */

        // elevation plot
        $lastPoint = null;
        $elevationLables = [];
        $elevationData = [];
        foreach ($gps->getVersions()->first()->getPoints() as $point) {
            /** @var $point Point */
            $distanceToShow = 150;
            if (!$lastPoint || ($point->getDistance() - $lastPoint->getDistance() > $distanceToShow)) {
                $elevationData[] = $point->getElevation();
                $elevationLables[] = number_format(
                    $point->getDistance() / 1000,
                    1,
                    '.',
                    ' '
                );
                $lastPoint = $point;
            }
        }

        return $this->render(
            'gps/view.html.twig',
            [
                'track' => $gps,
                'elevationData' => $elevationData,
                'elevationLables' => $elevationLables,
            ]
        );
    }

    public function download($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
                ->getRepository(TrackFile::class);

        $gps = $repo->findOneBy(['id' => $id]);
        $gps->getFileContent();

        $response = new \Symfony\Component\HttpFoundation\Response(
            $gps->getFileContent(),
            200,
            [
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="track.gpx";',
            ]
        );

        return $response;
    }
}
