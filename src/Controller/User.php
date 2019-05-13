<?php


namespace App\Controller;

use App\Form\Type\User\Terms;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class User extends AbstractController
{
    public function terms(Request $requesst)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('hwi_oauth_connect');
        }

        $form = $this->createForm(Terms::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($requesst);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->acceptTerms();
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('user/terms.html.twig', ['form' => $form->createView()]);
    }
}
