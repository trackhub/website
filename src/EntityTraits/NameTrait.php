<?php

namespace App\EntityTraits;

trait NameTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameEn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameBg;

    /**
     * Get localized name.
     * If localized name doesn't exists - fallback to EN
     */
    public function getName(string $locale): ?string
    {
        if ($locale === 'bg' && $this->nameBg !== null) {
            return $this->nameBg;
        }

        if ($this->nameEn === null) {
            return $this->nameBg;
        }

        return $this->nameEn;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function getNameBg(): ?string
    {
        return $this->nameBg;
    }

    public function setNameEn(string $nameEn = null): void
    {
        $this->nameEn = $nameEn;
    }

    public function setNameBg(string $nameBg = null): void
    {
        $this->nameBg = $nameBg;
    }
}
