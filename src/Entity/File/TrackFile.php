<?php

namespace App\Entity\File;

use App\Entity\Track;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Track")
     */
    private $track;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(Track $track, string $fileContent)
    {
        $this->track = $track;
        $this->fileContent = $fileContent;
        $this->createdAt = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getFileContent()
    {
        return $this->fileContent;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}