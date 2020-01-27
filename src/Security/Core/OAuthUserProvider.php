<?php

namespace App\Security\Core;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class OAuthUserProvider extends EntityUserProvider
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();



        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $id = $response->getUsername();
        var_dump($id);
//        $user = $this->findUser(array($this->properties[$resourceOwnerName] => $id));

//        var_dump($user);
//        die();

//        if ($user === null) {
//            /**
//             * @TODO: Try to fetch user from email
//             */
//            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $id));
//        }

        /**
         * If the user doesn't exist create a new one
         */
        $user = null;
//        var_dump($user);
//        die();
        if ($user === null) {
            $user = new \App\Entity\User();

            $user->setFacebookId($id);
            $user->acceptTerms();
            $user->setRoles(['ROLE_USER']);


            $this->em->persist($user);

            var_dump($user);
            die();

            $this->em->flush();



        }

        var_dump($user);
        die();
        return $user;



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

        var_dump($user);
        die();

        if (!$user) {
            // if user email is changed in 3rd party system, then change our data too
            // the problem is when user have login from facebook and google and emails are different :(
            if ($response->getEmail()) {
                $user = $this->userManager->findUserByEmail($response->getEmail());
            }

            if ($user) {
                $user->setFacebookId($idFromThirdParty);

                $this->em->persist($user);
                $this->em->flush();
            }
        }

        return $user;

        var_dump($user);
        die();

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
        }

        return $user;
    }
}
