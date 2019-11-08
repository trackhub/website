<?php

namespace App\Entity;

use App\Contract\CreatedAtInterface;
use App\Entity\Track\Image;
use App\Entity\Track\OptimizedPoint;
use App\Entity\Track\Version;
use App\Entity\User\User;
use App\Entity\Video\Youtube;
use App\EntityTraits\NameTrait;
use App\EntityTraits\SendByTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrackRepository")
 */
class Track implements CreatedAtInterface
{
    use NameTrait;
    use SendByTrait;

    public const TYPE_CYCLING = 1;
    public const TYPE_HIKING = 2;

    public const VALID_TYPES = [
        self::TYPE_CYCLING => self::TYPE_CYCLING,
        self::TYPE_HIKING => self::TYPE_HIKING,
    ];

    public const VISIBILITY_PUBLIC = 0;
    public const VISIBILITY_UNLISTED = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $visibility = 0;

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
     * Read-only property
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $descriptionEn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $descriptionBg;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = self::TYPE_CYCLING;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Track")
     * @ORM\JoinTable(name="track_uphill")
     */
    private $uphills;

    private $uphillVersionsCache;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Track")
     * @ORM\JoinTable(name="track_downhill")
     */
    private $downhills;

    private $downhillVersionsCache;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Video\Youtube", mappedBy="track", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $videosYoutube;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track\Image", mappedBy="track", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $images;

    public function __construct(User $sendBy)
    {
        $this->sendBy = $sendBy;
        $this->lastCheck = new DateTime();
        $this->optimizedPoints = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->uphills = new ArrayCollection();
        $this->downhills = new ArrayCollection();
        $this->videosYoutube = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
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

    public function addImage(Image $image)
    {
        $this->images->add($image);
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
     * @return Track[]
     */
    public function getUphills(): array
    {
        return $this->uphills->toArray();
    }

    public function addUphill(Track $track)
    {
        $this->uphills->add($track);
    }

    public function removeUphill(Track $track)
    {
        $this->uphills->removeElement($track);
    }

    public function addDownhill(Track $track)
    {
        $this->downhills->add($track);
    }

    public function removeDownhill(Track $track)
    {
        $this->downhills->removeElement($track);
    }

    /**
     * @return Track[]
     */
    public function getDownhills()
    {
        return $this->downhills->toArray();
    }

    public function getDownhillVersions($useCache = false, $ignoredTracks = []): array
    {
        if ($useCache && $this->downhillVersionsCache !== null) {
            return $this->downhillVersionsCache;
        }

        $ignoredTracks[] = $this;
        $versions = [];
        foreach ($this->getDownhills() as $downhills) {
            if (array_search($downhills, $ignoredTracks) !== false) {
                continue;
            }

            foreach ($downhills->getVersions() as $version) {
                $versions[] = $version;
            }

            foreach ($downhills->getDownhillVersions($useCache, $ignoredTracks) as $dhVersionsRecursive) {
                $versions[] = $dhVersionsRecursive;
            }
        }

        return $versions;
    }

    public function getUphillVersions($useCache = false, $ignoredTracks = []): array
    {
        if ($useCache && $this->uphillVersionsCache !== null) {
            return $this->uphillVersionsCache;
        }

        $ignoredTracks[] = $this;
        $versions = [];
        foreach ($this->getUphills() as $uphills) {
            if (array_search($uphills, $ignoredTracks) !== false) {
                continue;
            }

            foreach ($uphills->getVersions() as $version) {
                $versions[] = $version;
            }

            foreach ($uphills->getUphillVersions($useCache, $ignoredTracks) as $uphillVersionsRecursive) {
                $versions[] = $uphillVersionsRecursive;
            }
        }

        return $versions;
    }

    public function getSlugOrId(): string
    {
        if ($this->getSlug()) {
            return $this->getSlug();
        }

        return $this->getId();
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * @return Youtube[]|iterable
     */
    public function getVideosYoutube(): iterable
    {
        return $this->videosYoutube;
    }

    public function setVideosYoutube(iterable $videos)
    {
        $this->videosYoutube->clear();

        foreach ($videos as $video) {
            $this->addVideoYoutube($video);
        }
    }

    public function addVideoYoutube(Youtube $video)
    {
        $video->setTrack($this);
        $this->videosYoutube->add($video);
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(string $text = null): void
    {
        $this->descriptionEn = $text;
    }

    public function getDescriptionBg(): ?string
    {
        return $this->descriptionBg;
    }

    public function setDescriptionBg(string $text = null): void
    {
        $this->descriptionBg = $text;
    }

    /**
     * Get localized name.
     * If localized name doesn't exists - fallback to EN
     */
    public function getDescription(string $locale): ?string
    {
        if ($locale === 'bg' && $this->descriptionBg !== null) {
            return $this->descriptionBg;
        }

        if ($this->descriptionEn === null) {
            return $this->descriptionBg;
        }

        return $this->descriptionEn;
    }

    /**
     * @return Image[]|ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    public function __toString(): string
    {
        if ($this->getName('en')) {
            return $this->getName('en');
        }
        return $this->getId();
    }
}
