<?php

declare(strict_types=1);

namespace App\Service\TackHubApi\Response;

class PlaceImage
{
    public readonly string $placeId;
    public readonly string $filepath;

    public function __construct(string $placeId, string $filepath)
    {
        $this->placeId = $placeId;
        $this->filepath = $filepath;
    }
}
