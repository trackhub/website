<?php

namespace App\Twig;

use App\Entity\Track\Image;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TrackImageExtension extends AbstractExtension
{
    private $imagesDirectory;

    public function __construct(string $thumbnailsPath)
    {
        $this->imagesDirectory = $thumbnailsPath;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('app_track_image', [$this, 'resize']),
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
