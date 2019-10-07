<?php

namespace App\Entity\Track;

use App\Entity\File\TrackFile;
use App\Entity\Track;
use App\Entity\User\User;
use App\EntityTraits\SendByTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Version
{
    use SendByTrait;

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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\WayPoint", mappedBy="version", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $wayPoints;

    /**
     * @ORM\Column(type="integer")
     */
    private $positiveElevation = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $negativeElevation = 0;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Track\VersionRating",
     *     mappedBy="version",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $ratings;

    /**
     * @ORM\Column(type="enum_difficulty")
     */
    private $difficulty;

    public function __construct(User $sendBy)
    {
        $this->sendBy = $sendBy;
        $this->points = new ArrayCollection();
        $this->wayPoints = new ArrayCollection();
        $this->ratings = new ArrayCollection();
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

    public function addWayPoint(WayPoint $wp)
    {
        $wp->setVersion($this);
        $this->wayPoints->add($wp);
    }

    /**
     * @return WayPoint[]|ArrayCollection
     */
    public function getWayPoints()
    {
        return $this->wayPoints;
    }

    public function getFile(): ?TrackFile
    {
        return $this->file;
    }

    public function setFile(TrackFile $file): void
    {
        $this->file = $file;
    }

    public function getPositiveElevation(): int
    {
        return $this->positiveElevation;
    }

    public function setPositiveElevation(int $positiveElevation): void
    {
        $this->positiveElevation = $positiveElevation;
    }

    /**
     * @return int
     */
    public function getNegativeElevation(): int
    {
        return $this->negativeElevation;
    }

    public function setNegativeElevation(int $negativeElevation): void
    {
        $this->negativeElevation = $negativeElevation;
    }

    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * Get total number of votes
     *
     * @return int
     */
    public function getVotes(): int
    {
        return $this->ratings->count();
    }

    /**
     * Get version difficulty
     * @return string
     */
    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    /**
     * Set track difficulty
     *
     * @param $difficulty
     */
    public function setDifficulty(string $difficulty = null): void
    {
        $this->difficulty = $difficulty;
    }
}
