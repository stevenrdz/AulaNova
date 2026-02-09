<?php

namespace App\Controller;

use App\DTO\FileCompleteRequest;
use App\DTO\FilePresignRequest;
use App\Entity\FileObject;
use App\Service\AuditLogService;
use App\Service\FileStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/files')]
class FileController extends ApiController
{
    public function __construct(
        private readonly FileStorageService $fileStorageService,
        private readonly EntityManagerInterface $entityManager,
        private readonly AuditLogService $auditLogService
    ) {
    }

    #[Route('/presign', methods: ['POST'])]
    public function presign(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new FilePresignRequest();
        $dto->filename = $data['filename'] ?? '';
        $dto->mime_type = $data['mime_type'] ?? '';
        $dto->size = (int) ($data['size'] ?? 0);

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $result = $this->fileStorageService->createPresignedUpload($dto->filename, $dto->mime_type);

        return $this->json(['data' => $result]);
    }

    #[Route('/complete', methods: ['POST'])]
    public function complete(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new FileCompleteRequest();
        $dto->key = $data['key'] ?? '';
        $dto->bucket = $data['bucket'] ?? '';
        $dto->original_name = $data['original_name'] ?? '';
        $dto->mime_type = $data['mime_type'] ?? '';
        $dto->size = (int) ($data['size'] ?? 0);

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $fileObject = new FileObject($dto->key, $dto->bucket, $dto->original_name, $dto->mime_type, $dto->size);
        $user = $this->getUser();
        if ($user instanceof \App\Entity\User) {
            $fileObject->setCreatedBy($user);
        }

        $this->entityManager->persist($fileObject);
        $this->entityManager->flush();

        $this->auditLogService->log('create', 'file_object', (string) $fileObject->getId());

        return $this->json([
            'data' => [
                'id' => $fileObject->getId(),
                'key' => $fileObject->getObjectKey(),
                'bucket' => $fileObject->getBucket(),
                'original_name' => $fileObject->getOriginalName(),
                'mime_type' => $fileObject->getMimeType(),
                'size' => $fileObject->getSize(),
            ],
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $fileObject = $this->entityManager->getRepository(FileObject::class)->find($id);
        if (!$fileObject) {
            return $this->json(['message' => 'Archivo no encontrado.'], 404);
        }

        return $this->json([
            'data' => [
                'id' => $fileObject->getId(),
                'key' => $fileObject->getObjectKey(),
                'bucket' => $fileObject->getBucket(),
                'original_name' => $fileObject->getOriginalName(),
                'mime_type' => $fileObject->getMimeType(),
                'size' => $fileObject->getSize(),
            ],
        ]);
    }

    #[Route('/{id}/download', methods: ['GET'])]
    public function download(int $id, Request $request): JsonResponse
    {
        $fileObject = $this->entityManager->getRepository(FileObject::class)->find($id);
        if (!$fileObject) {
            return $this->json(['message' => 'Archivo no encontrado.'], 404);
        }

        $disposition = (string) $request->query->get('disposition', 'attachment');
        $filename = (string) $request->query->get('filename', $fileObject->getOriginalName());

        $result = $this->fileStorageService->createPresignedDownload(
            $fileObject->getObjectKey(),
            $fileObject->getMimeType(),
            $filename,
            $fileObject->getBucket(),
            $disposition
        );

        return $this->json(['data' => $result]);
    }

    #[Route('/{id}/stream', methods: ['GET'])]
    public function streamFile(int $id, Request $request): StreamedResponse|JsonResponse
    {
        $fileObject = $this->entityManager->getRepository(FileObject::class)->find($id);
        if (!$fileObject) {
            return $this->json(['message' => 'Archivo no encontrado.'], 404);
        }

        $stream = $this->fileStorageService->getObjectStream($fileObject->getObjectKey(), $fileObject->getBucket());
        if (!is_resource($stream)) {
            return $this->json(['message' => 'No se pudo abrir el archivo.'], 500);
        }

        $disposition = (string) $request->query->get('disposition', 'attachment');
        $filename = (string) $request->query->get('filename', $fileObject->getOriginalName());
        $disposition = strtolower($disposition) === 'inline' ? 'inline' : 'attachment';

        $response = new StreamedResponse(function () use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', $fileObject->getMimeType() ?: 'application/octet-stream');
        $response->headers->set('Content-Disposition', sprintf('%s; filename="%s"', $disposition, addcslashes($filename, '"\\')));

        return $response;
    }
}
