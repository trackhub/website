<?php

namespace App\EntityTraits;

use App\Entity\User\User;

trait SendByTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     */
    private $sendBy;

    public function getSendBy(): User
    {
        return $this->sendBy;
    }
}
