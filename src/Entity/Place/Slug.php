<?php

declare(strict_types=1);

namespace App\Entity\Place;

use App\Entity\Place;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="place_slug")
 */
class Slug
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     */
    private string $id;

    /**
     * @ORM\ManyToOne(targetEntity=Place::class)
     */
    private Place $place;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $slug;

    public function __construct(Place $place, string $slug)
    {
        $this->place = $place;
        $this->slug = $slug;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
