<?php

namespace App\Entity\Track;

use App\Entity\Track;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Track\SlugRepository")
 * @ORM\Table(name="track_slug")
 */
class Slug
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Track", inversedBy="versions")
     */
    private $track;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $slug;

    public function __construct(Track $track, string $slug)
    {
        $this->track = $track;
        $this->slug = $slug;
    }

    public function getTrack(): Track
    {
        return $this->track;
    }
}
