<?php

declare(strict_types=1);

namespace App\Service\TackHubApi\Response;

class Place
{
    private string $id;
    private string $slugOrId {
        get => $this->slugOrId;
    }

    public string $name {
        get => $this->name;
    }

    private string $icon;

    private float $lat;
    private float $lng;

    private bool $attraction;

    public function __construct(
        string $id,
        string $slugOrId,
        string $name,
        string $icon,
        float $lat,
        float $lng,
        bool $attraction
    ) {
        $this->id = $id;
        $this->slugOrId = $slugOrId;
        $this->name = $name;
        $this->icon = $icon;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->attraction = $attraction;
    }
}
