<?php

namespace App\Controller;

use App\DTO\AnuncioCreateRequest;
use App\DTO\AnuncioUpdateRequest;
use App\Entity\Anuncio;
use App\Entity\CursoVirtual;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/virtual/anuncios')]
#[IsGranted('ROLE_TEACHER')]
class AnuncioController extends ApiController
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

        $qb = $this->entityManager->getRepository(Anuncio::class)->createQueryBuilder('a')
            ->leftJoin('a.cursoVirtual', 'cv');

        if ($q !== '') {
            $qb->andWhere('LOWER(a.title) LIKE :q OR LOWER(a.content) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($cursoVirtualId !== null) {
            $qb->andWhere('cv.id = :curso_virtual_id')
                ->setParameter('curso_virtual_id', (int) $cursoVirtualId);
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
            'data' => array_map(fn (Anuncio $item) => $this->mapItem($item), $items),
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
        $dto = new AnuncioCreateRequest();
        $dto->curso_virtual_id = (int) ($data['curso_virtual_id'] ?? 0);
        $dto->title = $data['title'] ?? '';
        $dto->content = $data['content'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $cursoVirtual = $this->entityManager->getRepository(CursoVirtual::class)->find($dto->curso_virtual_id);
        if (!$cursoVirtual) {
            return $this->json(['message' => 'Curso virtual no encontrado.'], 400);
        }

        $item = new Anuncio($cursoVirtual, $dto->title, $dto->content);
        $user = $this->getUser();
        if ($user instanceof User) {
            $item->setCreatedBy($user);
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Anuncio::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AnuncioUpdateRequest();
        $dto->curso_virtual_id = $data['curso_virtual_id'] ?? null;
        $dto->title = $data['title'] ?? null;
        $dto->content = $data['content'] ?? null;

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
        if ($dto->content !== null) {
            $item->setContent($dto->content);
        }

        $item->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Anuncio::class)->find($id);
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

    private function mapItem(Anuncio $item): array
    {
        $cursoVirtual = $item->getCursoVirtual();
        $createdBy = $item->getCreatedBy();

        return [
            'id' => $item->getId(),
            'title' => $item->getTitle(),
            'content' => $item->getContent(),
            'created_at' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $item->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'curso_virtual' => [
                'id' => $cursoVirtual->getId(),
                'curso_id' => $cursoVirtual->getCurso()->getId(),
            ],
            'created_by' => $createdBy ? [
                'id' => $createdBy->getId(),
                'email' => $createdBy->getEmail(),
                'first_name' => $createdBy->getFirstName(),
                'last_name' => $createdBy->getLastName(),
            ] : null,
        ];
    }
}

