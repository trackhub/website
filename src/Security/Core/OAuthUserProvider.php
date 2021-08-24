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
            /**
             * @var string id in 3rd party system
             * For facebook this is user id
             */
            $idFromThirdParty = $response->getUsername();

            if (empty($idFromThirdParty)) {
                throw new AccountNotLinkedException(sprintf("User '%s' not found.", $idFromThirdParty));
            }
            $email = $response->getEmail();
            /**
             * @var $user User
             */
            $user = $this->findUser(['email' => $email]);

            if ($user) {
                $user->setFacebookId($idFromThirdParty);
            } else {
                $user = new User();
                $nickname = $response->getNickname();
                $user->setEmail($response->getEmail());
                $user->setFacebookId($idFromThirdParty);
                $user->setRoles(['ROLE_USER']);
                $user->enable();

                $usernameExists = true;
                $counter = 0;
                $nicknameTmp = $nickname;
                while ($usernameExists) {
                    if ($counter) {
                        $nicknameTmp = $nickname . $counter;
                    }

                    $existingUser = $this->repository->findOneBy(['nickname' => $nicknameTmp]);
                    if (!$existingUser) {
                        $usernameExists = false;
                    }

                    $counter++;
                }

                $user->setNickname($nicknameTmp);
            }

            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }
    }
}
