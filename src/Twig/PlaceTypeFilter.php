<?php

namespace App\Twig;

use App\Entity\Place;
use App\Translations;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PlaceTypeFilter extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('app_place_type', [$this, 'getType'])
        ];
    }

    /**
     * Get string corresponding to a place type
     *
     * @param int $type
     * @return string|null
     */
    public function getType(int $type): ?string
    {
        return Translations::placeTypeTranslationId($type);
    }
}
