<?php

declare(strict_types=1);

namespace App\Service\TackHubApi\Response;

class Place
{
    private string $id;
    private string $slugOrId;

    private string $name;

    private string $icon;

    private float $lat;
    private float $lng;

    private bool $attraction;
}