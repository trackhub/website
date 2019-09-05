<?php

namespace App\Controller\Track;

use App\Image\ImageEdit;
use App\Repository\TrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class Image extends AbstractController
{
    public function addImage(string $id, Request $request, TrackRepository $trackRepo, TranslatorInterface $translator)
    {
        $track = $trackRepo->findOneBy(['id' => $id]);

        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('file_upload', $token)) {
            throw $this->createAccessDeniedException('csrf check failed');
        }

        $file = $request->files->get('file');
        /* @var $file UploadedFile */
        if (!$file->isValid()) {
            return new Response(
                json_encode([
                    'status' => 1,
                    'error' => $translator->trans('Upload failed'),
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'text/json']
            );
        }

        $extension = $file->getClientOriginalExtension();
        $extension = mb_strtolower($extension);

        if (!in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
            return new Response(
                json_encode([
                    'status' => 1,
                    'error' => $translator->trans('Image format is not allowed'),
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'text/json']
            );
        }

        $uploadDirectory = $this->getParameter('track_images_directory') . DIRECTORY_SEPARATOR;
        $sqlFilepath = $track->getCreatedAt()->format('Y') . DIRECTORY_SEPARATOR . $track->getId();
        $uploadDirectory .= $sqlFilepath;

        $uploadFilename = uniqid() . '.' . $extension;
        $sqlFilepath .= DIRECTORY_SEPARATOR . $uploadFilename;

        $file->move(
            $uploadDirectory,
            $uploadFilename
        );

        $image = new \App\Entity\Track\Image(
            $sqlFilepath,
            $this->getUser(),
            $track
        );

        $this->getDoctrine()->getManager()->persist($image);

        $this->getDoctrine()->getManager()->flush();

        return new Response(
            json_encode(['status' => 0]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/json']
        );
    }

    /**
     * This method is called when thumbnails doesn't exists.
     * Thumbnail will be created and saved in the "public" directory.
     */
    public function generateThumbnail(int $year, string $trackId, string $imagePath, Request $request)
    {
        $originalImagePath = $this->getParameter('track_images_directory') . DIRECTORY_SEPARATOR;
        $originalImagePath .= $year . DIRECTORY_SEPARATOR . $trackId . DIRECTORY_SEPARATOR . $imagePath;

        $thumbnailPathDir = $this->getParameter('track_images_thumbnails_directory');
        $thumbnailPathDir .= DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $trackId;
        $thumbnailPath = $thumbnailPathDir . DIRECTORY_SEPARATOR . $imagePath;

        $resizer = new ImageEdit();
        $resizer->resize($originalImagePath, $thumbnailPath, 600, 300);

        return new Response(
            null,
            Response::HTTP_TEMPORARY_REDIRECT,
            [
                'location' => $request->getRequestUri(),
            ]
        );
    }
}
