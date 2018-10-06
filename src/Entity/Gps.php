<?php


namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Gps
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $lastCheck;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gps\Point", mappedBy="gps")
     */
    private $points;

    public function __construct()
    {
        $this->lastCheck = new DateTime();
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }
}