<?php

namespace App\Entity\User;


use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $termsAccepted;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebookId;

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    /**
     * @param string|null $facebookId
     */
    public function setFacebookId($facebookId): void
    {
        $this->facebookId = $facebookId;
    }

    public function isAcceptedTerms()
    {
        if ($this->termsAccepted === null) {
            return false;
        }

        return true;
    }

    public function acceptTerms()
    {
        $this->termsAccepted = new \DateTime();
    }
}
