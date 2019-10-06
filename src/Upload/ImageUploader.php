<?php

namespace App\Upload;

use App\Contract\CreatedAtInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImageUploader
{
    /**
     * Directory to store images at the server
     *
     * @var string
     */
    private $imagesDirectory;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(string $imagesDirectory, EntityRepository $repo, TranslatorInterface $translator)
    {
        $this->imagesDirectory = $imagesDirectory;
        $this->entityRepository = $repo;
        $this->translator = $translator;
    }

    /**
     * Entities are not persisted or flushed!
     *
     * @return array[entity, response]
     */
    public function addImage(string $id, Request $request, callable $entityCreator): array
    {
        $parentEntity = $this->entityRepository->findOneBy(['id' => $id]);

        if (!$parentEntity instanceof CreatedAtInterface) {
            throw new \Exception('Entity must implement SendAtInterface');
        }

        $file = $request->files->get('file');
        /* @var $file UploadedFile */
        if (!$file->isValid()) {
            return [
                null,
                new Response(
                    json_encode([
                        'status' => 1,
                        'error' => $this->translator->trans('Upload failed'),
                    ]),
                    Response::HTTP_BAD_REQUEST,
                    ['Content-Type' => 'text/json'],
                ),
            ];
        }

        $extension = $file->getClientOriginalExtension();
        $extension = mb_strtolower($extension);

        if (!in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
            return [
                null,
                new Response(
                    json_encode([
                        'status' => 1,
                        'error' => $this->translator->trans('Image format is not allowed'),
                    ]),
                    Response::HTTP_BAD_REQUEST,
                    ['Content-Type' => 'text/json'],
                ),
            ];
        }

        $uploadDirectory = $this->imagesDirectory . DIRECTORY_SEPARATOR;
        $sqlFilepath = $parentEntity->getCreatedAt()->format('Y') . DIRECTORY_SEPARATOR . $parentEntity->getId();
        $uploadDirectory .= $sqlFilepath;

        $uploadFilename = uniqid() . '.' . $extension;
        $sqlFilepath .= DIRECTORY_SEPARATOR . $uploadFilename;

        $file->move(
            $uploadDirectory,
            $uploadFilename
        );

        $image = $entityCreator($sqlFilepath, $parentEntity);

        return [
            $image,
            new Response(
                json_encode(['status' => 0]),
                Response::HTTP_OK,
                ['Content-Type' => 'text/json'],
            ),
        ];
    }
}
