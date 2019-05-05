<?php

namespace App\Entity\Track;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Point
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="`order`")
     */
    private $order;

    /**
     * @ORM\Column(type="float")
     */
    private $lat;

    /**
     * @ORM\Column(type="float")
     */
    private $lng;

    /**
     * @ORM\Column(type="float")
     */
    private $elevation = 0;

    /**
     * Distance so far
     *
     * @ORM\Column(type="float")
     */
    private $distance = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Track\Version", inversedBy="points")
     */
    private $version;

    /**
     * @param $order
     * @param $lat
     * @param $lng
     */
    public function __construct(int $order, float $lat, float $lng)
    {
        $this->order = $order;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function setElevation(float $elev)
    {
        $this->elevation = $elev;
    }

    public function getElevation(): ?float
    {
        return $this->elevation;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }
}
