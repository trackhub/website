<?php

namespace App\Event\Listener\User;

use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class Terms
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            return;
        }
        if ($user->isAcceptedTerms()) {
            // terms are accepted, continue with the page loading
            return;
        }

        $allowedUrls = [
            '/user/terms',
            '/privacy-policy',
            '/terms-of-service',
        ];

        $urlAllowed = false;
        $pathInfo = $event->getRequest()->getPathInfo();
        foreach ($allowedUrls as $url) {
            if (strpos($pathInfo, $url) === 3) {
                $urlAllowed = true;
                break;
            }
        }

        if ($urlAllowed === false) {
            $response = new \Symfony\Component\HttpFoundation\RedirectResponse('/en/user/terms', 302);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
