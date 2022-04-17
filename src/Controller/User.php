<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\User\Terms;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class User extends AbstractController
{
    public function terms(Request $request)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('hwi_oauth_connect');
        }

        $form = $this->createForm(Terms::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->acceptTerms();
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('user/terms.html.twig', ['form' => $form->createView()]);
    }

    public function downloadPersonalData(UserRepository $userRepository)
    {
        $user = $this->getUser();

        $userFromDb = $userRepository->findOneBy(['id' => $user->getid()]);

        // temp file
        $fp = fopen('php://temp', 'r+');
        fputcsv(
            $fp,
            [
                'username',
                'email',
                'facebook id',
            ]
        );
        fputcsv(
            $fp,
            [
                $userFromDb->getUsername(),
                $userFromDb->getEmail(),
                $userFromDb->getFacebookId(),
            ]
        );
        rewind($fp);
        $csvAsString = fread($fp, 1024 * 1024);
        // ... close the "file"...
        fclose($fp);

        return new Response(
            $csvAsString,
            200,
            [
                'content-type' => 'application/csv',
                'content-disposition' =>  'attachment; filename=personal-data.csv',
                'pragma' => 'no-cache',
            ]
        );
    }

    public function deleteAccount(UserRepository $userRepository, EntityManagerInterface $em, Request $request)
    {
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('confirm', CheckboxType::class);
        $formBuilder->add('submit', SubmitType::class);
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $userFromDb = $userRepository->findOneBy(['id' => $user->getid()]);
            $userFromDb->markForDeletion();
            $em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('user/deletion.html.twig', ['form' => $form->createView()]);
    }
}
