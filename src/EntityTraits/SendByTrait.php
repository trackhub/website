<?php

namespace App\EntityTraits;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

trait SendByTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'send_by_id', referencedColumnName: 'id', nullable: false)]
    private $sendBy;

    public function getSendBy(): User
    {
        return $this->sendBy;
    }
}
