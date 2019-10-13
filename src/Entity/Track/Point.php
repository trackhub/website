<?php

namespace App\Entity\Track;

use App\Entity\Point\ElevationPointTrait;
use App\Entity\Point\PointInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Point implements PointInterface
{
    use ElevationPointTrait;

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

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }
}
