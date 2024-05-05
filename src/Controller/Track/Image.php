<?php

namespace App\Controller\Track;

use App\Image\ImageEdit;
use App\Repository\TrackRepository;
use App\Upload\ImageUploader;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class Image extends AbstractController
{
    public function addImage(string $id, Request $request, TrackRepository $trackRepo, TranslatorInterface $translator)
    {
        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('file_upload', $token)) {
            throw $this->createAccessDeniedException('csrf check failed');
        }

        $uploader = new ImageUploader(
            $this->getParameter('track_images_directory'),
            $trackRepo,
            $translator,
        );

        $user = $this->getUser();

        $entityCreator = function ($path, $parentEntity) use ($user) {
            return new \App\Entity\Track\Image($path, $user, $parentEntity);
        };

        list($image, $response) = $uploader->addImage($id, $request, $entityCreator);

        if ($image) {
            $this->getDoctrine()->getManager()->persist($image);
            $this->getDoctrine()->getManager()->flush();
        }

        return $response;
    }

    /**
     * This method is called when thumbnails doesn't exists.
     * Thumbnail will be created and saved in the "public" directory.
     */
    public function generateThumbnail(int $year, string $trackId, string $imagePath, int $maxWidth, int $maxHeight, Request $request, LoggerInterface $logger)
    {
        $originalImagePath = $this->getParameter('track_images_directory') . DIRECTORY_SEPARATOR;
        $originalImagePath .= $year . DIRECTORY_SEPARATOR . $trackId . DIRECTORY_SEPARATOR . $imagePath;

        $thumbnailPathDir = $this->getParameter('track_images_thumbnails_directory');
        $thumbnailPathDir .= DIRECTORY_SEPARATOR . $maxWidth . DIRECTORY_SEPARATOR . $maxHeight;
        $thumbnailPathDir .= DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $trackId;
        $thumbnailPath = $thumbnailPathDir . DIRECTORY_SEPARATOR . $imagePath;

        $resizer = new ImageEdit($originalImagePath, $logger);
        $resizer->resize($maxWidth, $maxHeight);
        $resizer->watermark('track-hub.com');

        if ($resizer->shouldBeConverted()) {
            $resizer->convertToJpeg();
            $thumbnailPathArray = explode('.', $thumbnailPath);
            array_push($thumbnailPathArray, 'jpeg');
            $thumbnailPath = implode(".", $thumbnailPathArray);
        }

        $resizer->save($thumbnailPath);

        return new Response(
            null,
            Response::HTTP_TEMPORARY_REDIRECT,
            [
                'location' => $request->getRequestUri(),
            ]
        );
    }
}
