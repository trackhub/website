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

    /**
     * @param mixed $version
     */
    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }
}