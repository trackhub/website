<?php

namespace App\EntityTraits;

trait DescriptionTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $descriptionEn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $descriptionBg;

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(string $text = null): void
    {
        $this->descriptionEn = $text;
    }

    public function getDescriptionBg(): ?string
    {
        return $this->descriptionBg;
    }

    public function setDescriptionBg(string $text = null): void
    {
        $this->descriptionBg = $text;
    }

    /**
     * Get localized name.
     * If localized name doesn't exists - fallback to EN
     */
    public function getDescription(string $locale): ?string
    {
        if ($locale === 'bg' && $this->descriptionBg !== null) {
            return $this->descriptionBg;
        }

        if ($this->descriptionEn === null) {
            return $this->descriptionBg;
        }

        return $this->descriptionEn;
    }
}
