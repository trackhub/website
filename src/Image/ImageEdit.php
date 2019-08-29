<?php

namespace App\Image;

use Symfony\Component\Filesystem\Filesystem;

class ImageEdit
{
    public function resize(string $input, string $output, int $maxW, int $maxH)
    {
        $image = new \Imagick($input);
        $image->setImageOrientation($image->getImageOrientation());
        $image->adaptiveResizeImage($maxW, $maxH, true);

        $fs = new Filesystem();
        $fs->dumpFile($output, $image);
    }
}
