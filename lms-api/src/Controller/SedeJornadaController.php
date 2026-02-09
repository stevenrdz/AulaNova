<?php

namespace App\Controller;

use App\DTO\SedeJornadaCreateRequest;
use App\DTO\SedeJornadaUpdateRequest;
use App\Entity\SedeJornada;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/structure/sede-jornadas')]
#[IsGranted('ROLE_ADMIN')]
class SedeJornadaController extends ApiController
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
        $isActiveRaw = $request->query->get('is_active', null);
        $isActive = null;
        if ($isActiveRaw !== null) {
            $isActive = filter_var($isActiveRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $qb = $this->entityManager->getRepository(SedeJornada::class)->createQueryBuilder('s');

        if ($q !== '') {
            $qb->andWhere('LOWER(s.name) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($isActive !== null) {
            $qb->andWhere('s.isActive = :active')
                ->setParameter('active', $isActive);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('s.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (SedeJornada $item) => $this->mapItem($item), $items),
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
        $dto = new SedeJornadaCreateRequest();
        $dto->name = $data['name'] ?? '';
        $dto->is_active = $data['is_active'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $item = new SedeJornada();
        $item->setName($dto->name);
        if ($dto->is_active !== null) {
            $item->setIsActive($dto->is_active);
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(SedeJornada::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new SedeJornadaUpdateRequest();
        $dto->name = $data['name'] ?? null;
        $dto->is_active = $data['is_active'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->name !== null) {
            $item->setName($dto->name);
        }
        if ($dto->is_active !== null) {
            $item->setIsActive($dto->is_active);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(SedeJornada::class)->find($id);
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

    private function mapItem(SedeJornada $item): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'is_active' => $item->isActive(),
        ];
    }
}

