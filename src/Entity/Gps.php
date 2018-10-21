<?php


namespace App\Entity;

use App\Entity\Gps\Point;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Gps
{
    const TYPE_CYCLING = 1;
    const TYPE_HIKING = 2;

    const VALID_TYPES = [
        self::TYPE_CYCLING => self::TYPE_CYCLING,
        self::TYPE_HIKING => self::TYPE_HIKING,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $lastCheck;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gps\Point", mappedBy="gps", cascade={"persist"})
     */
    private $points;

    /**
     * @ORM\Column(type="float")
     */
    private $pointNorthEastLat = -999;

    /**
     * @ORM\Column(type="float")
     */
    private $pointNorthEastLng = -999;

    /**
     * @ORM\Column(type="float")
     */
    private $pointSouthWestLat = 999;

    /**
     * @ORM\Column(type="float")
     */
    private $pointSouthWestLng = 999;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GpsFile", mappedBy="gps")
     */
    private $files;

    public function __construct()
    {
        $this->lastCheck = new DateTime();
        $this->points = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Point[]
     */
    public function getPoints()
    {
        return $this->points;
    }

    public function addPoint(Point $p)
    {
        $p->setGps($this);
        $this->points->add($p);

        if ($p->getLat() > $this->pointNorthEastLat) {
            $this->pointNorthEastLat = $p->getLat();
        }

        if ($p->getLng() > $this->pointNorthEastLng) {
            $this->pointNorthEastLng = $p->getLng();
        }

        if ($p->getLat() < $this->pointSouthWestLat) {
            $this->pointSouthWestLat = $p->getLat();
        }

        if ($p->getLng() < $this->pointSouthWestLng) {
            $this->pointSouthWestLng = $p->getLng();
        }
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    private function calculateGetNorthEastLat()
    {
        $neLat = 0;
        $neLng = 9999;
        foreach ($this->getPoints() as $point) {
            if ($point->getLng() > $neLng) {
                $neLng = $point->getLng();
            }

            if ($point->getLat() < $neLat) {
                $neLat = $point->getLat();
            }
        }

        return [
            'lat' => $neLat,
            'lng' => $neLng,
        ];
    }
}