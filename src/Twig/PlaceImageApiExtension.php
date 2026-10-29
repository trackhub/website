<?php

namespace App\Twig;

use App\Service\TackHubApi\Response\PlaceImage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PlaceImageApiExtension extends AbstractExtension
{
    private $imagesDirectory;

    public function __construct(string $thumbnailsPath)
    {
        $this->imagesDirectory = $thumbnailsPath;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('app_place_image_api', [$this, 'resize']),
        ];
    }

    public function resize(PlaceImage $image, int $width, $height): string
    {
        $resizedPath = $this->imagesDirectory;
        $resizedPath .= '/' . $width . '/' . $height;
        $resizedPath .= '/' . $image->filepath;

        return $resizedPath;
    }
}
