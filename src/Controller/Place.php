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
        if ($form->isSubmitted() && $form->isValid()) {
            $place = new \App\Entity\Place(
                $form->get('lat')->getData(),
                $form->get('lng')->getData(),
            );

            $this->getDoctrine()->getManager()->persist($place);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_place_view', ['id' => $place->getId()]); // @FIXME!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }

        return $this->render(
            'place/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function edit(string $id, Request $request)
    {
        $placeRepo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);
        $place = $placeRepo->findOneBy(['id' => $id]);

        // @TODO ACL check!

        $form = $this->createForm(\App\Form\Type\Place::class);
        $form->add('submit', SubmitType::class);

        $form->get('lat')->setData($place->getLat());
        $form->get('lng')->setData($place->getLng());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $place->setLat($form->get('lat')->getData());
            $place->setLng($form->get('lng')->getData());

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('home'); // @FIXME!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }

        return $this->render(
            'place/edit.html.twig',
            [
                'form' => $form->createView(),
                'place' => $place,
            ]
        );
    }

    public function view($id)
    {
        $placeRepo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);
        $place = $placeRepo->findOneBy(['id' => $id]);

        return $this->render(
            'place/view.html.twig',
            [
                'place' => $place,
            ]
        );
    }
}
