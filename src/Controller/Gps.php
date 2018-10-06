<?php

namespace App\Controller;

use App\Entity\Gps\Point;
use App\Entity\GpsFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Gps extends AbstractController
{
    public function new(Request $request)
    {
        $form = $this->createForm(\App\Form\Type\Gps::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file');
            $fileData = $file->getData();
            /* @var $fileData UploadedFile */
            $c = file_get_contents($fileData->getRealPath());
            $n = $fileData->getClientOriginalName();

            /* @FIXME
             - only gps!
             */

            // we should have service for gpx processing
            $xml = simplexml_load_file($fileData->getRealPath());
            if ($xml === false) {
                throw new \RuntimeException("Xml load failed");
            }

            $gps = new \App\Entity\Gps();
            $gps->setType($form->get('type')->getData());

            foreach ($xml->trk as $track) {
                $order = 0;
                foreach ($track->trkseg as $tragSegment) {
                    foreach ($tragSegment->trkpt as $point) {
                        $attributes = $point->attributes();
                        $lat = (float)$attributes['lat'];
                        $lon = (float)$attributes['lon'];

                        $point = new Point(
                            $gps,
                            $order,
                            $lat,
                            $lon
                        );

                        $this->getDoctrine()->getManager()
                            ->persist($point);
                        $order++;
                    }
                }
            }
            $gpsFile = new GpsFile($gps, $c);
            $this->getDoctrine()->getManager()
                ->persist($gps);
            $this->getDoctrine()->getManager()
                ->persist($gpsFile);
            $this->getDoctrine()->getManager()
                ->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render(
            'gps/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function view($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
                ->getRepository(\App\Entity\Gps::class);

        $gps = $repo->findOneBy(['id' => $id]);


        return $this->render(
            'gps/view.html.twig',
            [
                'gps' => $gps,
            ]
        );
    }

    public function download($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository(\App\Entity\GpsFile::class);

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