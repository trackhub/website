<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Place extends AbstractController
{
    public function new()
    {
        $form = $this->createForm(\App\Form\Type\Place::class);

        return $this->render(
            'place/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
