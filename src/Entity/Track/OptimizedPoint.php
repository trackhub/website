<?php

namespace App\Entity\Track;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OptimizedPoint
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Track", inversedBy="optimizedPoints")
     */
    private $track;

    /**
     * Point constructor.
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
     * @param mixed $track
     */
    public function setTrack($track): void
    {
        $this->track = $track;
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