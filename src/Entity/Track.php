<?php


namespace App\Entity;

use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Point;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Track
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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     */
    private $lastCheck;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\Point", mappedBy="gps", cascade={"persist", "remove"}, orphanRemoval=true))
     */
    private $points;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\OptimizedPoint", mappedBy="gps", cascade={"persist", "remove"}, orphanRemoval=true))
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
     * @ORM\OneToMany(targetEntity="App\Entity\File\TrackFile", mappedBy="track")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $files;

    public function __construct()
    {
        $this->lastCheck = new DateTime();
        $this->points = new ArrayCollection();
        $this->optimizedPoints = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
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
     * @return Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function prepareForRecalculation()
    {
        $this->pointNorthEastLat = -999;
        $this->pointNorthEastLng = -999;
        $this->pointSouthWestLat = 999;
        $this->pointSouthWestLng = 999;

        $this->getPoints()->clear();
        $this->getOptimizedPoints()->clear();
    }
}
