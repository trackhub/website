<?php

namespace App\Controller;

use App\Entity\Gps\Point;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class Home extends AbstractController
{
    public function home()
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getRepository(\App\Entity\Gps::class);
        $gpsCollection = $em->findAll();

        return $this->render(
            'home/home.html.twig',
            [
                'gpsCollection' => $gpsCollection,
            ]
        );
    }
}