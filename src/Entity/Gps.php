<?php


namespace App\Entity;

use App\Entity\Gps\OptimizedPoint;
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
     * @ORM\OneToMany(targetEntity="App\Entity\Gps\OptimizedPoint", mappedBy="gps", cascade={"persist"})
     */
    private $optimizedPoints;

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
        $this->optimizedPoints = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Point[]|ArrayCollection
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return mixed
     */
    public function getPointNorthEastLat()
    {
        return $this->pointNorthEastLat;
    }

    /**
     * @return mixed
     */
    public function getPointNorthEastLng()
    {
        return $this->pointNorthEastLng;
    }

    /**
     * @return mixed
     */
    public function getPointSouthWestLat()
    {
        return $this->pointSouthWestLat;
    }

    /**
     * @return mixed
     */
    public function getPointSouthWestLng()
    {
        return $this->pointSouthWestLng;
    }

    /**
     * @return OptimizedPoint[]
     */
    public function getOptimizedPoints()
    {
        return $this->optimizedPoints;
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

    public function addOptimizedPoint(OptimizedPoint $p)
    {
        $p->setGps($this);
        $this->optimizedPoints->add($p);
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