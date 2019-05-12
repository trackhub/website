<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SimpleContent extends AbstractController
{
    public function privacyPolicy()
    {
        return $this->render('simple-content/privacyPolicy.twig');
    }

    public function legalBases()
    {
        return $this->render('simple-content/legalBases.html.twig');
    }

    public function cookiePolicy()
    {
        return $this->render('simple-content/cookiePolicy.html.twig');
    }

    public function termsOfService()
    {
        return $this->render('simple-content/termsOfService.html.twig');
    }
}
