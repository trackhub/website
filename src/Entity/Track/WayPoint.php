<?php

namespace App\Entity\Track;

use App\Entity\Point\ElevationPointTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="version_waypoint")
 */
class WayPoint
{
    use ElevationPointTrait;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Track\Version", inversedBy="wayPoints")
     */
    private $version;

    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null)
    {
        $this->name = $name;
    }
}
