<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class Place extends AbstractController
{
    public function new(Request $request)
    {
        $form = $this->createForm(\App\Form\Type\Place::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $place = new \App\Entity\Place(
                $form->get('lat')->getData(),
                $form->get('lng')->getData(),
            );

            $this->getDoctrine()->getManager()->persist($place);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('home'); // @FIXME!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }

        return $this->render(
            'place/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
