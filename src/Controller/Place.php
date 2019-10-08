<?php

namespace App\Controller;

use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class Place extends AbstractController
{

    public function index(Request $request, PaginatorInterface $paginator)
    {
        $repo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);

        /** @var QueryBuilder $qb */
        $qb = $repo->createQueryBuilder('g')->orderBy('g.createdAt', 'DESC');

        $places = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10);

        return $this->render(
            'place/index.html.twig',
            [
//                'places' => [],
                'places' => $places,
            ]
        );
    }

    public function new(Request $request)
    {
        $form = $this->createForm(\App\Form\Type\Place::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $place = new \App\Entity\Place(
                $form->get('lat')->getData(),
                $form->get('lng')->getData(),
                $this->getUser(),
            );

            if (!$form->get('nameEn')->isEmpty()) {
                $place->setNameEn($form->get('nameEn')->getData());
            }

            if (!$form->get('nameBg')->isEmpty()) {
                $place->setNameBg($form->get('nameBg')->getData());
            }

            $place->setType($form->get('type')->getData());

            $this->getDoctrine()->getManager()->persist($place);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_place_view', ['id' => $place->getId()]);
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

        $this->denyAccessUnlessGranted('edit', $place);

        $form = $this->createForm(\App\Form\Type\Place::class);
        $form->add('submit', SubmitType::class);

        $form->get('lat')->setData($place->getLat());
        $form->get('lng')->setData($place->getLng());
        $form->get('nameEn')->setData($place->getNameEn());
        $form->get('nameBg')->setData($place->getNameBg());
        $form->get('type')->setData($place->getType());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $place->setLat($form->get('lat')->getData());
            $place->setLng($form->get('lng')->getData());
            $place->setNameEn($form->get('nameEn')->getData());
            $place->setNameBg($form->get('nameBg')->getData());
            $place->setType($form->get('type')->getData());

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_place_view', ['id' => $place->getId()]);
        }

        return $this->render(
            'place/edit.html.twig',
            [
                'form' => $form->createView(),
                'place' => $place,
            ]
        );
    }

    public function view(string $id, Request $request)
    {
        $placeRepo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);
        $place = $placeRepo->findOneBy(['id' => $id]);

        return $this->render(
            'place/view.html.twig',
            [
                'place' => $place,
                'app_title' => $place->getName($request->getLocale()),
                'canEdit' => $this->isGranted('edit', $place),
            ]
        );
    }
}
