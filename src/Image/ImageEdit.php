<?php

namespace App\Image;

use Imagick;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageEdit
{
    private $input;
    private $image;

    private LoggerInterface $logger;

    public function __construct(string $input, LoggerInterface $logger)
    {
        $this->input = $input;
        $this->logger = $logger;
        $this->image = new Imagick($input);

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

    public function shouldBeConverted(): bool
    {
        $format = $this->image->getImageFormat();

        $convertableFormats = [
            'HEIC',
        ];

        return in_array($format, $convertableFormats);
    }

    public function convertToJpeg()
    {
        $this->image->setImageFormat('jpeg');
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
        $exifData = [];
        try {
            $exifData = exif_read_data($this->input);
        } catch (\Exception $e) {
            $this->logger->warning(
                'unable to read exif data',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }

        if (isset($exifData['Orientation'])) {
            switch ($exifData['Orientation']) {
                case 3:
                case 4: // + mirrored
                    return 180;
                case 5:
                case 6: // + mirrored
                    return 90;
                case 7:
                case 8: // + mirrored
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
