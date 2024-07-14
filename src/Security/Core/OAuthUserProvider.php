<?php

namespace App\Security\Core;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as HwiOAuthUserProvider;

class OAuthUserProvider implements OAuthAwareUserProviderInterface
{
    private EntityManagerInterface $em;

    private HwiOAuthUserProvider $hwiOAuthUserProvider;

    public function __construct(ManagerRegistry $registry, HwiOAuthUserProvider $authUserProvider)
    {
        $this->em = $registry->getManager();
        $this->hwiOAuthUserProvider = $authUserProvider;
     }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response): ?UserInterface
    {
        try {
            return $this->hwiOAuthUserProvider->loadUserByOAuthUserResponse($response);
        } catch (UserNotFoundException $e) {
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
