<?php

namespace App\Entity;

use App\Contract\CreatedAtInterface;
use App\Entity\Point\PointTrait;
use App\Entity\Place\Image;
use App\Entity\User\User;
use App\EntityTraits\NameTrait;
use App\EntityTraits\SendByTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlaceRepository")
 * @ORM\Table(name="place")
 */
class Place implements CreatedAtInterface
{
    use PointTrait;
    use NameTrait;
    use SendByTrait;

    public const TYPE_GENERIC = 0;
    public const TYPE_DRINKING_FOUNTAIN = 1;
    public const TYPE_DRINKING_RESTAURANT = 2;
    public const TYPE_DRINKING_HOTEL = 3;
    public const TYPE_BIKE_SHOP = 4;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Place\Image", mappedBy="place", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $images;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = self::TYPE_GENERIC;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(float $lat, float $lng, User $sendBy)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->sendBy = $sendBy;
        $this->createdAt = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setLat(float $lat)
    {
        $this->lat = $lat;
    }

    public function setLng(float $lng)
    {
        $this->lng = $lng;
    }

    public function addImage(Image $image)
    {
        $this->images->add($image);
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * @return \ArrayAccess|Image[]
     */
    public function getImages(): iterable
    {
        return $this->images;
    }
}
