<?php

namespace App\Entity\Track;

use App\Entity\Track;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Track\ImageRepository")
 * @ORM\Table(name="track_image")
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Track", inversedBy="optimizedPoints")
     */
    private $track;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     */
    private $sendBy;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $filepath;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(string $file, User $sendBy, Track $track)
    {
        $this->filepath = $file;
        $this->sendBy = $sendBy;
        $this->track = $track;
        $this->createdAt = new \DateTime();
        $track->addImage($this);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function getTrack(): Track
    {
        return $this->track;
    }
}
