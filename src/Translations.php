<?php

namespace App;

class Translations
{
    public static function placeType(): array
    {
        return [
            \App\Entity\Place::TYPE_GENERIC => 'place.type.generic',
            \App\Entity\Place::TYPE_DRINKING_FOUNTAIN => 'place.type.drinking_fountain',
            \App\Entity\Place::TYPE_DRINKING_RESTAURANT => 'place.type.restaurant',
            \App\Entity\Place::TYPE_DRINKING_HOTEL =>  'place.type.hotel',
            \App\Entity\Place::TYPE_BIKE_SHOP => 'place.type.bike_shop',
        ];
    }

    public static function placeTypeValueTypes(): array
    {
        return array_flip(static::placeType());
    }

    public static function placeTypeTranslationId(int $type)
    {
        $types = static::placeType();
        return $types[$type] ?? null;
    }
}
