<?php

namespace App\Security\Authorization\Voter;

use App\Entity\Track;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TrackVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof Track) {
            return false;
        }

        switch ($attribute) {
            case 'edit':
                return true;
        }

        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /* @var $subject Track */

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($subject->getSendBy() === $token->getUser()) {
            return true;
        }

        return false;
    }
}
