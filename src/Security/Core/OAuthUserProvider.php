<?php

namespace App\Security\Core;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;

class OAuthUserProvider extends FOSUBUserProvider
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        /**
         * @var string id in 3rd party system
         * For facebook this is user id
         */
        $idFromThirdParty = $response->getUsername();

        if (empty($idFromThirdParty)) {
            throw new AccountNotLinkedException(sprintf("User '%s' not found.", $idFromThirdParty));
        }

        $field = $this->getProperty($response);
        $user = $this->userManager->findUserBy([$field => $idFromThirdParty]);

        if (!$user) {
            // if user email is changed in 3rd party system, then change our data too
            // the problem is when user have login from facebook and google and emails are different :(
            if ($response->getEmail()) {
                $user = $this->userManager->findUserByEmail($response->getEmail());
            }

            if ($user) {
                $user->setFacebookId($idFromThirdParty);
            }
        }

        if (!$user) {
            $user = new \App\Entity\User\User();
            $user->setFacebookId($idFromThirdParty);

            $usernameFromThirdParty = $response->getNickname();
            $username = $usernameFromThirdParty;
            $usernameExists = true;
            $counter = 0;
            while ($usernameExists) {
                if ($counter) {
                    $username = $usernameFromThirdParty . $counter;
                }

                $existingUser = $this->userManager->findUserByUsername($username);
                if (!$existingUser) {
                    $usernameExists = false;
                }

                $counter++;
            }

            $user->setUsername($username);
            $user->setEmail($response->getEmail());
            $user->setPassword(
                sha1(
                    base64_encode(random_bytes(30))
                )
            );
            $user->setEnabled(true);
            $this->userManager->updateUser($user);

            $user = $this->loadUserByUsername($response->getNickname());
        }

        return $user;
    }
}
