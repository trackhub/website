<?php

namespace App\Entity\Track;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Rating
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Track\Version",
     *     inversedBy="rating"
     * )
     */
    private $version;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\User",
     *     inversedBy="rating"
     *     )
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $rating;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating)
    {
        $this->rating = $rating;
    }
}
