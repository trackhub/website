<?php

namespace App\Security\Core;

use App\Entity\User\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class OAuthUserProvider extends EntityUserProvider
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            return parent::loadUserByOAuthUserResponse($response);
        } catch (UsernameNotFoundException $e) {
            $email = $response->getEmail();
            /**
             * @var $user User
             */
            $user = $this->findUser(['email' => $email]);

            if ($user) {
                $user->setFacebookId($response->getUsername());
            } else {
                $user = new User();
                $user->setNickname($response->getNickname());
                $user->setEmail($response->getEmail());
                $user->setFacebookId($response->getUsername());
                $user->setRoles(['ROLE_USER']);
                $user->setEnabled(true);
            }

            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }
    }
}
