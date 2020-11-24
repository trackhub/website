<?php

namespace App\Place;

use App\Entity\Place;

class ImageDetector
{
    public function getImage(int $placeType): ?string
    {
        switch ($placeType) {
            case Place::TYPE_DRINKING_FOUNTAIN:
                return '/images/trackhub/water/icon.png';
            case Place::TYPE_DRINKING_RESTAURANT:
                return '/images/trackhub/restaurant/icon.png';
        }

        return null;
    }
}
