<?php

namespace App\Controller\Place;

use App\Image\ImageEdit;
use App\Repository\PlaceRepository;
use App\Upload\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class Image extends AbstractController
{
    public function addImage(string $id, Request $request, PlaceRepository $placeRepo, TranslatorInterface $translator)
    {
        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('file_upload', $token)) {
            throw $this->createAccessDeniedException('csrf check failed');
        }

        $uploader = new ImageUploader(
            $this->getParameter('place_images_directory'),
            $placeRepo,
            $translator,
        );

        $user = $this->getUser();

        $entityCreator = function ($path, $parentEntity) use ($user) {
            return new \App\Entity\Place\Image($path, $user, $parentEntity);
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
    public function generateThumbnail(int $year, string $placeId, string $imagePath, int $maxWidth, int $maxHeight, Request $request)
    {
        $originalImagePath = $this->getParameter('place_images_directory') . DIRECTORY_SEPARATOR;
        $originalImagePath .= $year . DIRECTORY_SEPARATOR . $placeId . DIRECTORY_SEPARATOR . $imagePath;

        $thumbnailPathDir = $this->getParameter('place_images_thumbnails_directory');
        $thumbnailPathDir .= DIRECTORY_SEPARATOR . $maxWidth . DIRECTORY_SEPARATOR . $maxHeight;
        $thumbnailPathDir .= DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $placeId;
        $thumbnailPath = $thumbnailPathDir . DIRECTORY_SEPARATOR . $imagePath;

        $resizer = new ImageEdit();
        $resizer->resize($originalImagePath, $thumbnailPath, $maxWidth, $maxHeight);

        return new Response(
            null,
            Response::HTTP_TEMPORARY_REDIRECT,
            [
                'location' => $request->getRequestUri(),
            ]
        );
    }
}
