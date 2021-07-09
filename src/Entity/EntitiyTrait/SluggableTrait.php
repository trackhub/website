<?php
declare(strict_types=1);

namespace App\Entity\EntitiyTrait;

trait SluggableTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
