<?php

namespace App\Image;

use Symfony\Component\Filesystem\Filesystem;

class ImageEdit
{
    public function resize(string $input, string $output, int $maxW, int $maxH)
    {
        $image = new \Imagick($input);
        $image->setImageOrientation($image->getImageOrientation());

        $resizedW = min($image->getImageWidth(), $maxW);
        $resizedH = min($image->getImageHeight(), $maxH);

        $image->adaptiveResizeImage($resizedW, $resizedH, true);

        $fs = new Filesystem();
        $fs->dumpFile($output, $image);
    }
}
