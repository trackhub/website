<?php

namespace App\Entity\Track;

use App\Entity\File\TrackFile;
use App\Entity\Track;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Version
{
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
     * @ORM\OneToOne(targetEntity="App\Entity\File\TrackFile")
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Track", inversedBy="versions")
     */
    private $track;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\Point", mappedBy="version", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $points;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function setTrack(Track $track): void
    {
        $this->track = $track;
    }

    /**
     * @return Point[]|ArrayCollection
     */
    public function getPoints()
    {
        return $this->points;
    }

    public function addPoint(Point $p)
    {
        $p->setVersion($this);
        $this->points->add($p);

//        if ($p->getLat() > $this->pointNorthEastLat) {
//            $this->pointNorthEastLat = $p->getLat();
//        }
//
//        if ($p->getLng() > $this->pointNorthEastLng) {
//            $this->pointNorthEastLng = $p->getLng();
//        }
//
//        if ($p->getLat() < $this->pointSouthWestLat) {
//            $this->pointSouthWestLat = $p->getLat();
//        }
//
//        if ($p->getLng() < $this->pointSouthWestLng) {
//            $this->pointSouthWestLng = $p->getLng();
//        }
    }

    /**
     * @return Collection
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile(TrackFile $file): void
    {
        $this->file = $file;
    }
}
