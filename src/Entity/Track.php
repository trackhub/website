<?php


namespace App\Entity;

use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Version;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     */
    private $lastCheck;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\Version", mappedBy="track", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $versions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\OptimizedPoint", mappedBy="track", cascade={"persist", "remove"}, orphanRemoval=true))
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
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = self::TYPE_CYCLING;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Track")
     */
    private $uphills;

    public function __construct()
    {
        $this->lastCheck = new DateTime();
        $this->optimizedPoints = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->uphills = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPointNorthEastLat(): ?float
    {
        return $this->pointNorthEastLat;
    }

    public function getPointNorthEastLng(): ?float
    {
        return $this->pointNorthEastLng;
    }

    public function getPointSouthWestLat(): ?float
    {
        return $this->pointSouthWestLat;
    }

    public function getPointSouthWestLng(): ?float
    {
        return $this->pointSouthWestLng;
    }

    /**
     * @return OptimizedPoint[]|ArrayCollection
     */
    public function getOptimizedPoints()
    {
        return $this->optimizedPoints;
    }

    public function addOptimizedPoint(OptimizedPoint $p)
    {
        $p->setTrack($this);
        $this->optimizedPoints->add($p);
    }

    public function addOptimizedPoints(iterable $collection)
    {
        foreach ($collection as $point) {
            $this->addOptimizedPoint($point);
        }
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function prepareForRecalculation()
    {
        $this->pointNorthEastLat = -999;
        $this->pointNorthEastLng = -999;
        $this->pointSouthWestLat = 999;
        $this->pointSouthWestLng = 999;

        $this->getOptimizedPoints()->clear();
    }

    public function addVersion(Version $version)
    {
        $version->setTrack($this);
        $this->versions->add($version);
    }

    /**
     * @return ArrayCollection|Version[]
     */
    public function getVersions()
    {
        return $this->versions;
    }

    public function recalculateEdgesCache()
    {
        $this->pointNorthEastLat = -999;
        $this->pointNorthEastLng = -999;
        $this->pointSouthWestLat = 999;
        $this->pointSouthWestLng = 999;

        foreach ($this->versions->first()->getPoints() as $p) {
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
    }

    /**
     * @return ArrayCollection|Track[]
     */
    public function getUphills()
    {
        return $this->uphills;
    }

    public function addUphill(Track $track)
    {
        $this->uphills->add($track);
    }

    public function __toString(): string
    {
        return $this->getName() || $this->getId();
    }
}
