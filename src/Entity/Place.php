<?php

namespace App\Entity;

use App\Entity\Point\PointTrait;
use App\EntityTraits\NameableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlaceRepository")
 * @ORM\Table(name="place")
 */
class Place
{
    use PointTrait;
    use NameableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
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
}
