<?php

namespace App\Controller;

use App\DTO\CursoVirtualCreateRequest;
use App\DTO\CursoVirtualUpdateRequest;
use App\Entity\Curso;
use App\Entity\CursoVirtual;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/virtual/cursos')]
#[Security("is_granted('ROLE_TEACHER') or is_granted('ROLE_STUDENT')")]
class CursoVirtualController extends ApiController
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
        $cursoId = $request->query->get('curso_id');

        $qb = $this->entityManager->getRepository(CursoVirtual::class)->createQueryBuilder('cv')
            ->leftJoin('cv.curso', 'c');

        if ($q !== '') {
            $qb->andWhere('LOWER(c.name) LIKE :q OR LOWER(cv.description) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($cursoId !== null) {
            $qb->andWhere('c.id = :curso_id')
                ->setParameter('curso_id', (int) $cursoId);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(cv.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('cv.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (CursoVirtual $item) => $this->mapItem($item), $items),
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
        $dto = new CursoVirtualCreateRequest();
        $dto->curso_id = (int) ($data['curso_id'] ?? 0);
        $dto->description = $data['description'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $curso = $this->entityManager->getRepository(Curso::class)->find($dto->curso_id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado.'], 400);
        }

        $item = new CursoVirtual($curso);
        $item->setDescription($dto->description);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_TEACHER')]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(CursoVirtual::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new CursoVirtualUpdateRequest();
        $dto->curso_id = $data['curso_id'] ?? null;
        $dto->description = $data['description'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->curso_id !== null) {
            $curso = $this->entityManager->getRepository(Curso::class)->find($dto->curso_id);
            if (!$curso) {
                return $this->json(['message' => 'Curso no encontrado.'], 400);
            }
            $item->setCurso($curso);
        }

        if ($dto->description !== null) {
            $item->setDescription($dto->description);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_TEACHER')]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(CursoVirtual::class)->find($id);
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

    private function mapItem(CursoVirtual $item): array
    {
        $curso = $item->getCurso();

        return [
            'id' => $item->getId(),
            'description' => $item->getDescription(),
            'created_at' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            'curso' => [
                'id' => $curso->getId(),
                'name' => $curso->getName(),
            ],
        ];
    }
}

