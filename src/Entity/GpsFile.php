<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GpsFile
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
     * @ORM\ManyToOne(targetEntity="Gps")
     */
    private $gps;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct($gps, $fileContent)
    {
        $this->gps = $gps;
        $this->fileContent = $fileContent;
        $this->createdAt = new \DateTime();
    }
}