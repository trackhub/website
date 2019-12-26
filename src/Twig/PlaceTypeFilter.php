<?php

namespace App\Twig;

use App\Entity\Place;
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
        switch ($type) {
            case Place::TYPE_GENERIC:
                return "Generic";
            case Place::TYPE_DRINKING_FOUNTAIN:
                return "Drinking fountain";
            case Place::TYPE_DRINKING_RESTAURANT:
                return "Restaurant";
            case Place::TYPE_DRINKING_HOTEL:
                return "Hotel";
            case Place::TYPE_BIKE_SHOP:
                return "Bike shop";
            default:
                return null;
        }
    }
}
