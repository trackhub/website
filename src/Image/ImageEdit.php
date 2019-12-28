<?php

namespace App\Image;

use Symfony\Component\Filesystem\Filesystem;

class ImageEdit
{
    private $input;
    private $image;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->image = new \Imagick($input);

        $this->image->setImageOrientation($this->image->getImageOrientation());
        $rotateAngle = $this->getRotateAngle();
        if ($rotateAngle !== 0) {
            $this->image->rotateImage('#FFFFFF', $rotateAngle);
        }

        $profiles = $this->image->getImageProfiles("icc", true);

        // clear personal data stored in meta information
        $this->image->stripImage();

        // restore icc color profile
        if (isset($profiles['icc'])) {
            $this->image->profileImage("icc", $profiles['icc']);
        }
    }

    public function resize(int $maxW, int $maxH)
    {
        $resizedW = min($this->image->getImageWidth(), $maxW);
        $resizedH = min($this->image->getImageHeight(), $maxH);

        $this->image->adaptiveResizeImage($resizedW, $resizedH, true);
    }

    public function watermark(string $text)
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor('gray');
        $fontSize = $this->image->getImageWidth() / 100 * 3;
        $draw->setFontSize($fontSize);

        $y = $this->image->getImageHeight() - $fontSize / 1.7;
        $x = $this->image->getImageWidth() - strlen($text) * $fontSize / 1.7;

        $this->image->annotateImage($draw, $x, $y, 0, $text);
    }

    public function getRotateAngle(): int
    {
        $exifData = exif_read_data($this->input);

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

    public function save(string $path)
    {
        $fs = new Filesystem();
        $fs->dumpFile($path, $this->image);
    }
}
