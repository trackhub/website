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

        $rotateAngle = $this->getRotateAngle($input);
        if ($rotateAngle !== 0) {
            $image->rotateImage('#FFFFFF', $rotateAngle);
        }

        $profiles = $image->getImageProfiles("icc", true);

        // clear personal data stored in meta information
        $image->stripImage();

        // restore icc color profile
        if (isset($profiles['icc'])) {
            $image->profileImage("icc", $profiles['icc']);
        }

        $fs = new Filesystem();
        $fs->dumpFile($output, $image);
    }

    public function getRotateAngle($imagePath): int
    {
        $exifData = exif_read_data($imagePath);

        if (isset($exifData['Orientation'])) {
            switch ($exifData['Orientation']) {
                case 6:
                    return 90;
                case 8:
                    return 270;
            }
        }

        return 0;
    }
}
