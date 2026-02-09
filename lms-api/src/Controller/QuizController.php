<?php

namespace App\Controller;

use App\DTO\QuizCreateRequest;
use App\DTO\QuizUpdateRequest;
use App\Entity\CursoVirtual;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/assessments/quizzes')]
#[Security("is_granted('ROLE_TEACHER') or is_granted('ROLE_STUDENT')")]
class QuizController extends ApiController
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

        $qb = $this->entityManager->getRepository(Quiz::class)->createQueryBuilder('qz')
            ->leftJoin('qz.cursoVirtual', 'cv');

        if ($q !== '') {
            $qb->andWhere('LOWER(qz.title) LIKE :q OR LOWER(qz.description) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($cursoVirtualId !== null) {
            $qb->andWhere('cv.id = :curso_virtual_id')
                ->setParameter('curso_virtual_id', (int) $cursoVirtualId);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(qz.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('qz.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Quiz $item) => $this->mapItem($item), $items),
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
        $dto = new QuizCreateRequest();
        $dto->curso_virtual_id = (int) ($data['curso_virtual_id'] ?? 0);
        $dto->title = $data['title'] ?? '';
        $dto->description = $data['description'] ?? null;
        $dto->start_at = $data['start_at'] ?? null;
        $dto->end_at = $data['end_at'] ?? null;
        $dto->time_limit_minutes = $data['time_limit_minutes'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $cursoVirtual = $this->entityManager->getRepository(CursoVirtual::class)->find($dto->curso_virtual_id);
        if (!$cursoVirtual) {
            return $this->json(['message' => 'Curso virtual no encontrado.'], 400);
        }

        $startAt = $this->parseDateTime($dto->start_at);
        if ($dto->start_at !== null && $startAt === null) {
            return $this->dateTimeValidationError('start_at');
        }

        $endAt = $this->parseDateTime($dto->end_at);
        if ($dto->end_at !== null && $endAt === null) {
            return $this->dateTimeValidationError('end_at');
        }

        if ($startAt && $endAt && $endAt < $startAt) {
            return $this->json(['message' => 'La fecha fin debe ser posterior a inicio.'], 400);
        }

        $item = new Quiz($cursoVirtual, $dto->title);
        $item->setDescription($dto->description)
            ->setStartAt($startAt)
            ->setEndAt($endAt)
            ->setTimeLimitMinutes($dto->time_limit_minutes);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_TEACHER')]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Quiz::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new QuizUpdateRequest();
        $dto->curso_virtual_id = $data['curso_virtual_id'] ?? null;
        $dto->title = $data['title'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->start_at = $data['start_at'] ?? null;
        $dto->end_at = $data['end_at'] ?? null;
        $dto->time_limit_minutes = $data['time_limit_minutes'] ?? null;

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

        if ($dto->title !== null) {
            $item->setTitle($dto->title);
        }

        if ($dto->description !== null) {
            $item->setDescription($dto->description);
        }

        if ($dto->start_at !== null) {
            $startAt = $this->parseDateTime($dto->start_at);
            if ($startAt === null) {
                return $this->dateTimeValidationError('start_at');
            }
            $item->setStartAt($startAt);
        }

        if ($dto->end_at !== null) {
            $endAt = $this->parseDateTime($dto->end_at);
            if ($endAt === null) {
                return $this->dateTimeValidationError('end_at');
            }
            $item->setEndAt($endAt);
        }

        $startAt = $item->getStartAt();
        $endAt = $item->getEndAt();
        if ($startAt && $endAt && $endAt < $startAt) {
            return $this->json(['message' => 'La fecha fin debe ser posterior a inicio.'], 400);
        }

        if ($dto->time_limit_minutes !== null) {
            $item->setTimeLimitMinutes($dto->time_limit_minutes);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_TEACHER')]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Quiz::class)->find($id);
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

    private function mapItem(Quiz $item): array
    {
        $cursoVirtual = $item->getCursoVirtual();

        return [
            'id' => $item->getId(),
            'title' => $item->getTitle(),
            'description' => $item->getDescription(),
            'start_at' => $item->getStartAt()?->format('Y-m-d H:i:s'),
            'end_at' => $item->getEndAt()?->format('Y-m-d H:i:s'),
            'time_limit_minutes' => $item->getTimeLimitMinutes(),
            'curso_virtual' => [
                'id' => $cursoVirtual->getId(),
                'curso_id' => $cursoVirtual->getCurso()->getId(),
            ],
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

