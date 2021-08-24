<?php

namespace App\Controller;

use App\Entity\Place\Slug;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class Place extends AbstractController
{
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $repo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);

        /** @var QueryBuilder $qb */
        $qb = $repo->createQueryBuilder('g')->orderBy('g.createdAt', 'DESC');
        $places = $paginator->paginate($qb, $request->query->getInt('page', 1), 10);

        return $this->render(
            'place/index.html.twig',
            [
                'places' => $places,
            ]
        );
    }

    public function new(Request $request, EntityManagerInterface $em)
    {
        $slugRepo = $em->getRepository(Slug::class);

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
            if ($form->get('isAttraction')) {
                $place->makeAttraction();
            }

            $place->setDescriptionBg($form->get('descriptionBg')->getData());
            $place->setDescriptionEn($form->get('descriptionEn')->getData());

            $formIsValid = true;
            if (!$form->get('slug')->isEmpty()) {
                $slug = $form->get('slug')->getData();

                if ($place->getSlug() !== $slug) {
                    $place->setSlug($slug);
                    $existingSlug = $slugRepo->findOneBy(['slug' => $slug]);
                    if ($existingSlug) {
                        $formIsValid = false;
                        $form->get('slug')->addError(
                            new FormError('Slug is already taken')
                        );
                    } else {
                        $slugEntity = new Slug($place, $slug);
                        $this->getDoctrine()->getManager()
                            ->persist($slugEntity);
                    }
                }
            }

            if ($formIsValid) {
                $em->persist($place);
                $em->flush();

                return $this->redirectToRoute('app_place_view', ['id' => $place->getId()]);
            }
        }

        return $this->render(
            'place/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function edit(string $id, Request $request, EntityManagerInterface $em)
    {
        $placeRepo = $this->getDoctrine()->getRepository(\App\Entity\Place::class);
        $slugRepo = $em->getRepository(\App\Entity\Place\Slug::class);
        $place = $placeRepo->findOneBy(['id' => $id]);

        $this->denyAccessUnlessGranted('edit', $place);

        $form = $this->createForm(\App\Form\Type\Place::class);
        $form->add('submit', SubmitType::class);

        $form->get('lat')->setData($place->getLat());
        $form->get('lng')->setData($place->getLng());
        $form->get('nameEn')->setData($place->getNameEn());
        $form->get('nameBg')->setData($place->getNameBg());
        $form->get('type')->setData($place->getType());
        $form->get('descriptionEn')->setData($place->getDescriptionEn());
        $form->get('descriptionBg')->setData($place->getDescriptionBg());
        $form->get('isAttraction')->setData($place->isAttraction());
        $form->get('slug')->setData($place->getSlug());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $place->setLat($form->get('lat')->getData());
            $place->setLng($form->get('lng')->getData());
            $place->setNameEn($form->get('nameEn')->getData());
            $place->setNameBg($form->get('nameBg')->getData());
            $place->setType($form->get('type')->getData());
            $place->setDescriptionBg($form->get('descriptionBg')->getData());
            $place->setDescriptionEn($form->get('descriptionEn')->getData());
            if ($form->get('isAttraction')->getData()) {
                $place->makeAttraction();
            } else {
                $place->makeRegular();
            }

            $formIsValid = true;
            if (!$form->get('slug')->isEmpty()) {
                $slug = $form->get('slug')->getData();

                if ($place->getSlug() !== $slug) {
                    $place->setSlug($slug);
                    $existingSlug = $slugRepo->findOneBy(['slug' => $slug]);
                    if ($existingSlug) {
                        if ($existingSlug->getPlace() !== $place) {
                            $formIsValid = false;
                            $form->get('slug')->addError(
                                new FormError('Slug is already taken')
                            );
                        }
                    } else {
                        $slugEntity = new Slug($place, $slug);
                        $this->getDoctrine()->getManager()
                            ->persist($slugEntity);
                    }
                }
            }

            if ($formIsValid) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('app_place_view', ['id' => $place->getId()]);
            }
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
        if (!$place) {
            $slug = $this->getDoctrine()->getRepository(Slug::class)->findOneBy(['slug' => $id]);
            if (!$slug) {
                throw $this->createNotFoundException();
            }
            $place = $slug->getPlace();
        }

        $canonicalUrl = null;
        if ($place->getSlug()) {
            $canonicalUrl = $this->generateUrl(
                'app_place_view',
                ['id' => $place->getSlug()],
            );
        }

        return $this->render(
            'place/view.html.twig',
            [
                'place' => $place,
                'canEdit' => $this->isGranted('edit', $place),
                'app_title' => $place->getName($request->getLocale()),
                'app_canonical_url' => $canonicalUrl,
            ]
        );
    }
}
