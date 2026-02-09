<?php

namespace App\Controller;

use App\DTO\ActividadCreateRequest;
use App\DTO\ActividadUpdateRequest;
use App\Entity\Actividad;
use App\Entity\ActividadAttachment;
use App\Entity\CursoVirtual;
use App\Entity\FileObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/virtual/actividades')]
#[Security("is_granted('ROLE_TEACHER') or is_granted('ROLE_STUDENT')")]
class ActividadController extends ApiController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $q = trim((string) $request->query->get('q', ''));
        $cursoVirtualId = $request->query->get('curso_virtual_id');
        $type = $request->query->get('type');

        $qb = $this->entityManager->getRepository(Actividad::class)->createQueryBuilder('a')
            ->leftJoin('a.cursoVirtual', 'cv');

        if ($q !== '') {
            $qb->andWhere('LOWER(a.title) LIKE :q OR LOWER(a.content) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($cursoVirtualId !== null) {
            $qb->andWhere('cv.id = :curso_virtual_id')
                ->setParameter('curso_virtual_id', (int) $cursoVirtualId);
        }

        if ($type !== null && $type !== '') {
            $qb->andWhere('a.type = :type')
                ->setParameter('type', strtoupper((string) $type));
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Actividad $item) => $this->mapItem($item), $items),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_TEACHER')]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new ActividadCreateRequest();
        $dto->curso_virtual_id = (int) ($data['curso_virtual_id'] ?? 0);
        $dto->type = strtoupper((string) ($data['type'] ?? ''));
        $dto->title = $data['title'] ?? '';
        $dto->content = $data['content'] ?? null;
        $dto->youtube_url = $data['youtube_url'] ?? null;
        $dto->file_id = $data['file_id'] ?? null;
        $dto->is_graded = $data['is_graded'] ?? null;
        $dto->due_at = $data['due_at'] ?? null;
        $dto->attachment_ids = $data['attachment_ids'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $cursoVirtual = $this->entityManager->getRepository(CursoVirtual::class)->find($dto->curso_virtual_id);
        if (!$cursoVirtual) {
            return $this->json(['message' => 'Curso virtual no encontrado.'], 400);
        }

        $dueAt = $this->parseDateTime($dto->due_at);
        if ($dto->due_at !== null && $dueAt === null) {
            return $this->dateTimeValidationError('due_at');
        }

        $item = new Actividad($cursoVirtual, $dto->type, $dto->title);
        $item->setContent($dto->content)
            ->setYoutubeUrl($dto->youtube_url)
            ->setIsGraded($dto->is_graded ?? false)
            ->setDueAt($dueAt);

        if ($dto->file_id !== null) {
            $file = $this->entityManager->getRepository(FileObject::class)->find($dto->file_id);
            if (!$file) {
                return $this->json(['message' => 'Archivo no encontrado.'], 400);
            }
            $item->setFile($file);
        }

        $this->entityManager->persist($item);

        $attachments = [];
        if (is_array($dto->attachment_ids)) {
            foreach ($dto->attachment_ids as $fileId) {
                $file = $this->entityManager->getRepository(FileObject::class)->find((int) $fileId);
                if (!$file) {
                    return $this->json(['message' => 'Archivo adjunto no encontrado.'], 400);
                }
                $attachment = new ActividadAttachment($item, $file);
                $this->entityManager->persist($attachment);
                $attachments[] = $attachment;
            }
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item, $attachments)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_TEACHER')]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Actividad::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new ActividadUpdateRequest();
        $dto->curso_virtual_id = $data['curso_virtual_id'] ?? null;
        $dto->type = isset($data['type']) ? strtoupper((string) $data['type']) : null;
        $dto->title = $data['title'] ?? null;
        $dto->content = $data['content'] ?? null;
        $dto->youtube_url = $data['youtube_url'] ?? null;
        $dto->file_id = $data['file_id'] ?? null;
        $dto->is_graded = $data['is_graded'] ?? null;
        $dto->due_at = $data['due_at'] ?? null;
        $dto->attachment_ids = $data['attachment_ids'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->curso_virtual_id !== null) {
            $cursoVirtual = $this->entityManager->getRepository(CursoVirtual::class)->find($dto->curso_virtual_id);
            if (!$cursoVirtual) {
                return $this->json(['message' => 'Curso virtual no encontrado.'], 400);
            }
            $item->setCursoVirtual($cursoVirtual);
        }

        if ($dto->type !== null) {
            $item->setType(strtoupper($dto->type));
        }

        if ($dto->title !== null) {
            $item->setTitle($dto->title);
        }

        if ($dto->content !== null) {
            $item->setContent($dto->content);
        }

        if ($dto->youtube_url !== null) {
            $item->setYoutubeUrl($dto->youtube_url);
        }

        if ($dto->is_graded !== null) {
            $item->setIsGraded($dto->is_graded);
        }

        if ($dto->due_at !== null) {
            $dueAt = $this->parseDateTime($dto->due_at);
            if ($dueAt === null) {
                return $this->dateTimeValidationError('due_at');
            }
            $item->setDueAt($dueAt);
        }

        if ($dto->file_id !== null) {
            $file = $this->entityManager->getRepository(FileObject::class)->find($dto->file_id);
            if (!$file) {
                return $this->json(['message' => 'Archivo no encontrado.'], 400);
            }
            $item->setFile($file);
        }

        $attachments = null;
        if (is_array($dto->attachment_ids)) {
            $attachments = [];
            $existing = $this->entityManager->getRepository(ActividadAttachment::class)
                ->findBy(['actividad' => $item]);
            foreach ($existing as $attachment) {
                $this->entityManager->remove($attachment);
            }

            foreach ($dto->attachment_ids as $fileId) {
                $file = $this->entityManager->getRepository(FileObject::class)->find((int) $fileId);
                if (!$file) {
                    return $this->json(['message' => 'Archivo adjunto no encontrado.'], 400);
                }
                $attachment = new ActividadAttachment($item, $file);
                $this->entityManager->persist($attachment);
                $attachments[] = $attachment;
            }
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item, $attachments)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_TEACHER')]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Actividad::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        try {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            return $this->json(['message' => 'No se puede eliminar el registro.'], 409);
        }

        return $this->json(['message' => 'Registro eliminado']);
    }

    private function mapItem(Actividad $item, ?array $attachments = null): array
    {
        $cursoVirtual = $item->getCursoVirtual();
        $file = $item->getFile();

        if ($attachments === null) {
            $attachments = $this->entityManager->getRepository(ActividadAttachment::class)
                ->findBy(['actividad' => $item]);
        }

        return [
            'id' => $item->getId(),
            'type' => $item->getType(),
            'title' => $item->getTitle(),
            'content' => $item->getContent(),
            'youtube_url' => $item->getYoutubeUrl(),
            'is_graded' => $item->isGraded(),
            'due_at' => $item->getDueAt()?->format('Y-m-d H:i:s'),
            'created_at' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            'curso_virtual' => [
                'id' => $cursoVirtual->getId(),
                'curso_id' => $cursoVirtual->getCurso()->getId(),
            ],
            'file' => $file ? $this->mapFile($file) : null,
            'attachments' => array_map(fn (ActividadAttachment $attachment) => $this->mapFile($attachment->getFile()), $attachments),
        ];
    }

    private function mapFile(FileObject $file): array
    {
        return [
            'id' => $file->getId(),
            'key' => $file->getObjectKey(),
            'bucket' => $file->getBucket(),
            'original_name' => $file->getOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    private function parseDateTime(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function dateTimeValidationError(string $field): JsonResponse
    {
        return $this->json([
            'message' => 'Validation failed',
            'errors' => [
                $field => ['Formato inv?lido. Usa una fecha/hora v?lida.'],
            ],
        ], 422);
    }
}

