<?php

namespace App\Entity\Track;

use App\Entity\File\TrackFile;
use App\Entity\Track;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     */
    private $sendBy;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\File\TrackFile", cascade={"persist", "remove"}, orphanRemoval=true)
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

    public function __construct(User $sendBy)
    {
        $this->sendBy = $sendBy;
        $this->points = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setTrack(Track $track): void
    {
        $this->track = $track;
    }

    public function getTrack(): ?Track
    {
        return $this->track;
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
    }

    public function getFile(): ?TrackFile
    {
        return $this->file;
    }

    public function setFile(TrackFile $file): void
    {
        $this->file = $file;
    }
}
