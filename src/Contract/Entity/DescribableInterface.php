<?php

namespace App\Contract\Entity;

/**
 * This interface provide automatic htmlpurifier on persist/update
 */
interface DescribableInterface
{
    public function getDescriptionEn(): ?string;

    public function setDescriptionEn(string $text = null): void;

    public function getDescriptionBg(): ?string;

    public function setDescriptionBg(string $text = null): void;
}
