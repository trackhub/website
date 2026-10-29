<?php

declare(strict_types=1);

namespace App\Service\TackHubApi\Response;

class Place
{
    private string $id;

    public readonly ?string $slug;
    public readonly string $slugOrId;

    public readonly ?string $description;

    public readonly string $name;

    public readonly string $icon;

    public readonly float $lat;
    public readonly float $lng;

    public readonly bool $attraction;

    public readonly int $type;

    public readonly array $images;

    public function __construct(
        string $id,
        string $slugOrId,
        ?string $slug,
        string $name,
        string $icon,
        float $lat,
        float $lng,
        bool $attraction,
        int $type,
        ?string $description,
        array $images,
    ) {
        $this->id = $id;
        $this->slugOrId = $slugOrId;
        $this->slug = $slug;
        $this->name = $name;
        $this->icon = $icon;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->attraction = $attraction;
        $this->type = $type;
        $this->description = $description;
        $this->addImages($images);
    }

    private function addImages(array $images): void
    {
        $allImages = [];
        foreach ($images as $image) {
            if (!$image instanceof PlaceImage) {
                throw new \RuntimeException('wrong instance');
            }
            $allImages[] = $image;
        }
        $this->images = $allImages;
    }
}
