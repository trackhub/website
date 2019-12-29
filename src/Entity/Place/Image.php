<?php

namespace App\Entity\Place;

use App\Entity\Place;
use App\Entity\User\User;
use App\EntityTraits\SendByTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Place\ImageRepository")
 * @ORM\Table(name="place_image")
 */
class Image
{
    use SendByTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="images")
     */
    private $place;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $filepath;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(string $file, User $sendBy, Place $place)
    {
        $this->filepath = $file;
        $this->sendBy = $sendBy;
        $this->place = $place;
        $this->createdAt = new \DateTime();
        $place->addImage($this);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
