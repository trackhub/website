<?php

namespace App\Entity\Video;

use App\Entity\Track;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="video_youtube")
 */
class Youtube
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Track", inversedBy="videosYoutube")
     */
    private $track;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $link;

    public function __construct(string $link)
    {
        $this->link = $link;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setTrack(Track $track)
    {
        $this->track = $track;
    }
}
