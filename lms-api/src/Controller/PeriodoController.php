<?php

namespace App\Controller;

use App\DTO\PeriodoCreateRequest;
use App\DTO\PeriodoUpdateRequest;
use App\Entity\Periodo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/structure/periodos')]
#[IsGranted('ROLE_ADMIN')]
class PeriodoController extends ApiController
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

        $qb = $this->entityManager->getRepository(Periodo::class)->createQueryBuilder('p');

        if ($q !== '') {
            $qb->andWhere('LOWER(p.name) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Periodo $item) => $this->mapItem($item), $items),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new PeriodoCreateRequest();
        $dto->name = $data['name'] ?? '';
        $dto->start_date = $data['start_date'] ?? null;
        $dto->end_date = $data['end_date'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $startDate = $this->parseDate($dto->start_date);
        if ($dto->start_date !== null && $startDate === null) {
            return $this->dateValidationError('start_date');
        }

        $endDate = $this->parseDate($dto->end_date);
        if ($dto->end_date !== null && $endDate === null) {
            return $this->dateValidationError('end_date');
        }

        $item = new Periodo();
        $item->setName($dto->name)
            ->setStartDate($startDate)
            ->setEndDate($endDate);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Periodo::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new PeriodoUpdateRequest();
        $dto->name = $data['name'] ?? null;
        $dto->start_date = $data['start_date'] ?? null;
        $dto->end_date = $data['end_date'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->name !== null) {
            $item->setName($dto->name);
        }

        if ($dto->start_date !== null) {
            $startDate = $this->parseDate($dto->start_date);
            if ($startDate === null) {
                return $this->dateValidationError('start_date');
            }
            $item->setStartDate($startDate);
        }

        if ($dto->end_date !== null) {
            $endDate = $this->parseDate($dto->end_date);
            if ($endDate === null) {
                return $this->dateValidationError('end_date');
            }
            $item->setEndDate($endDate);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Periodo::class)->find($id);
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

    private function mapItem(Periodo $item): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'start_date' => $item->getStartDate()?->format('Y-m-d'),
            'end_date' => $item->getEndDate()?->format('Y-m-d'),
        ];
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$date) {
            return null;
        }

        return $date->setTime(0, 0);
    }

    private function dateValidationError(string $field): JsonResponse
    {
        return $this->json([
            'message' => 'Validation failed',
            'errors' => [
                $field => ['Formato inválido. Usa YYYY-MM-DD.'],
            ],
        ], 422);
    }
}

