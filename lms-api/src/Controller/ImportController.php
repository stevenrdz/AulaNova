<?php

namespace App\Controller;

use App\DTO\ImportUsersRequest;
use App\Entity\ImportBatch;
use App\Entity\ImportFile;
use App\Entity\ImportRowError;
use App\Entity\FileObject;
use App\Entity\User;
use App\Message\ImportUsersMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/imports')]
#[IsGranted('ROLE_ADMIN')]
class ImportController extends ApiController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/users', methods: ['POST'])]
    public function importUsers(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new ImportUsersRequest();
        $dto->file_id = (int) ($data['file_id'] ?? 0);

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $file = $this->entityManager->getRepository(FileObject::class)->find($dto->file_id);
        if (!$file) {
            return $this->json(['message' => 'Archivo no encontrado.'], 400);
        }

        $batch = new ImportBatch('users');
        $batch->setCreatedBy($user);

        $importFile = new ImportFile($batch, $file);

        $this->entityManager->persist($batch);
        $this->entityManager->persist($importFile);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new ImportUsersMessage($batch->getId()));

        return $this->json(['data' => $this->mapBatch($batch)], 201);
    }

    #[Route('/batches', methods: ['GET'])]
    public function listBatches(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $type = $request->query->get('type');
        $status = $request->query->get('status');

        $qb = $this->entityManager->getRepository(ImportBatch::class)->createQueryBuilder('b')
            ->leftJoin('b.createdBy', 'u')
            ->leftJoin('b.resultFile', 'rf');

        if ($type) {
            $qb->andWhere('b.type = :type')->setParameter('type', $type);
        }
        if ($status) {
            $qb->andWhere('b.status = :status')->setParameter('status', $status);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('b.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $batches = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (ImportBatch $batch) => $this->mapBatch($batch), $batches),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ]);
    }

    #[Route('/batches/{id}', methods: ['GET'])]
    public function showBatch(int $id): JsonResponse
    {
        $batch = $this->entityManager->getRepository(ImportBatch::class)->find($id);
        if (!$batch) {
            return $this->json(['message' => 'Batch no encontrado.'], 404);
        }

        $errors = $this->entityManager->getRepository(ImportRowError::class)
            ->findBy(['batch' => $batch], ['id' => 'ASC']);

        return $this->json([
            'data' => $this->mapBatch($batch),
            'errors' => array_map(fn (ImportRowError $error) => [
                'id' => $error->getId(),
                'row_number' => $error->getRowNumber(),
                'message' => $error->getMessage(),
                'raw_data' => $error->getRawData(),
            ], $errors),
        ]);
    }

    private function mapBatch(ImportBatch $batch): array
    {
        $createdBy = $batch->getCreatedBy();
        $resultFile = $batch->getResultFile();

        return [
            'id' => $batch->getId(),
            'type' => $batch->getType(),
            'status' => $batch->getStatus(),
            'total_rows' => $batch->getTotalRows(),
            'success_count' => $batch->getSuccessCount(),
            'error_count' => $batch->getErrorCount(),
            'created_at' => $batch->getCreatedAt()->format('Y-m-d H:i:s'),
            'created_by' => $createdBy ? [
                'id' => $createdBy->getId(),
                'email' => $createdBy->getEmail(),
                'first_name' => $createdBy->getFirstName(),
                'last_name' => $createdBy->getLastName(),
            ] : null,
            'result_file' => $resultFile ? [
                'id' => $resultFile->getId(),
                'key' => $resultFile->getObjectKey(),
                'bucket' => $resultFile->getBucket(),
                'original_name' => $resultFile->getOriginalName(),
                'mime_type' => $resultFile->getMimeType(),
                'size' => $resultFile->getSize(),
            ] : null,
        ];
    }
}
