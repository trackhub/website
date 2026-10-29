<?php

namespace App\Entity\Track;

use App\Entity\Track;
use App\Entity\User\User;
use App\EntityTraits\SendByTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\Track\ImageRepository::class)]
#[ORM\Table(name: 'track_image')]
class Image
{
    use SendByTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(name: 'id', type: 'guid')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Track::class, inversedBy: 'images')]
    private $track;

    #[ORM\Column(type: 'string', nullable: false)]
    private $filepath;

    #[ORM\Column(type: 'datetime')]
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
