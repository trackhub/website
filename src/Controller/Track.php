<?php

namespace App\Controller;

use App\Entity\File\TrackFile;
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


            $gps = new \App\Entity\Track();
            // we should have service for gpx processing
            $processor = new Processor();
            $processor->process($c, $gps);

            $gps->setType($form->get('type')->getData());
            $gps->setName($form->get('name')->getData());

            if ($gps->getPoints()->isEmpty()) {
                $form->get('file')->addError(
                    new FormError('error') // @FIXME translate
                );
            } else {
                $gpsFile = new TrackFile($gps, $c);
                $this->getDoctrine()->getManager()
                    ->persist($gps);
                $this->getDoctrine()->getManager()
                    ->persist($gpsFile);
                $this->getDoctrine()->getManager()
                    ->flush();

                return $this->redirectToRoute('index');
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
        $gps = $this->getDoctrine()->getRepository(\App\Entity\Track::class)->findOneBy(['id' => $id]);

        return $this->render(
            'gps/newVersion.html.twig',
            [
                'gps' => $gps,
            ]
        );
    }

    public function view($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
                ->getRepository(\App\Entity\Track::class);

        $gps = $repo->findOneBy(['id' => $id]);


        return $this->render(
            'gps/view.html.twig',
            [
                'track' => $gps,
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
