<?php

namespace App\Entity\User;

use App\Entity\Track\VersionRating;
use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Track\VersionRating",
     *     mappedBy="user",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $ratings;

    public function __construct()
    {
        parent::__construct();

        $this->ratings = new ArrayCollection();
    }

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

    /**
     * @return ArrayCollection|VersionRating[]
     */
    public function getRatings()
    {
        return $this->ratings;
    }
}
