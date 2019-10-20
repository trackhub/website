<?php

namespace App\Tests\functionalTests\test\Helper;

use App\Entity\User\User;

class UserGenerator
{
    public function generateUser($username = null, $email = null)
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);

        $this->em->persist($user);

        return $user;
    }
}
