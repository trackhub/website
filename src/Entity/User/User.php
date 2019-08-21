<?php

namespace App\Entity\User;

use App\Entity\Track\Rating;
use App\Entity\Track\Version;
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
     *     targetEntity="App\Entity\Track\Rating",
     *     mappedBy="user"
     * )
     */
    private $rating;

    public function __construct()
    {
        $this->rating = new ArrayCollection();
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
     * @return ArrayCollection|Rating[]
     */
    public function getRating()
    {
        return $this->rating;
    }

    public function addRating(Version $version)
    {
        if ($this->rating->contains($version)) {
            return;
        }

        $this->rating[] = $version;
        $version->addRating($this);
    }

    public function removeRating(Version $version)
    {
        if (!$this->rating->contains($version)) {
            return;
        }

        $this->rating->removeElement($version);
        $version->removeRating($this);

    }
}
