<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Place extends AbstractController
{
    public function new()
    {
        return $this->render(
            'place/edit.html.twig',
            [
            ]
        );
    }
}
