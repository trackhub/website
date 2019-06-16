<?php

namespace App\Entity\File;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Track\Version;

/**
 * @ORM\Entity
 */
class TrackFile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $fileContent;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Track\Version")
     */
    private $version;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(Version $version, string $fileContent)
    {
        $this->version = $version;
        $this->fileContent = $fileContent;
        $this->createdAt = new \DateTime();
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }
}
