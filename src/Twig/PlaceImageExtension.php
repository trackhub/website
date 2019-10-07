<?php

namespace App\Twig;

use App\Entity\Place\Image;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PlaceImageExtension extends AbstractExtension
{
    private $imagesDirectory;

    public function __construct(string $thumbnailsPath)
    {
        $this->imagesDirectory = $thumbnailsPath;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('app_place_image', [$this, 'resize']),
        ];
    }

    public function resize(Image $image, int $width, $height): string
    {
        $resizedPath = $this->imagesDirectory;
        $resizedPath .= '/' . $width . '/' . $height;
        $resizedPath .= '/' . $image->getFilepath();

        return $resizedPath;
    }
}
