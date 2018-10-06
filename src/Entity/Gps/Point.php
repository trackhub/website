<?php

namespace App\Entity\Gps;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Gps;

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
    private $lon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gps")
     */
    private $gps;

    /**
     * Point constructor.
     * @param $order
     * @param $lat
     * @param $lon
     */
    public function __construct(Gps $gps, int $order, float $lat, float $lon)
    {
        $this->gps = $gps;
        $this->order = $order;
        $this->lat = $lat;
        $this->lon = $lon;
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
    public function getLon()
    {
        return $this->lon;
    }
}