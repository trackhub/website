<?php

namespace App\Controller;

use App\Entity\Gps\Point;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class Home extends AbstractController
{
    public function home()
    {
        return $this->render(
            'home/home.html.twig',
            []
        );
    }

    public function find($neLat, $neLon, $swLat, $swLon, int $size)
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getRepository(\App\Entity\Gps::class);
        /* @var $em EntityRepository */

        $responseData = [];

        for ($i = 0; $i < $size; $i++) {
            $qb = $em->createQueryBuilder('g');
            $qb->select('count(g.id)');
            $q = $qb->getQuery();
            $data = $q->getSingleResult();
            $count = current($data);
            if ($count > 10) {
                // @TODO add "zoom please"
            } else {
                $qb = $em->createQueryBuilder('g');
                $qb->select('g');
                $q = $qb->getQuery();
                $qResult = $q->getResult();
                /* @var $qResult \App\Entity\Gps[] */
                foreach($qResult as $gps) {
                    $gpsArrayData = [];
                    $gpsArrayData['id'] = $gps->getId();
                    foreach ($gps->getPoints() as $point) {
                        /* @var $point Point */
                        $gpsArrayData['points'][] = [
                            'lat' => $point->getLat(),
                            'lng' => $point->getLng(),
                        ];
                    }

                    $responseData[] = $gpsArrayData;
                }
            }
        }



        return new Response(
            json_encode($responseData)
        );
    }
}