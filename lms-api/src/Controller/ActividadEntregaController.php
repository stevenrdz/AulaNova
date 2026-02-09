<?php

namespace App\Controller;

use App\DTO\ActividadEntregaCreateRequest;
use App\DTO\ActividadEntregaGradeRequest;
use App\Entity\Actividad;
use App\Entity\ActividadEntrega;
use App\Entity\FileObject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/virtual/actividades')]
class ActividadEntregaController extends ApiController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/{id}/submissions', methods: ['GET'])]
    #[Security("is_granted('ROLE_TEACHER') or is_granted('ROLE_ADMIN')")]
    public function index(int $id, Request $request): JsonResponse
    {
        $actividad = $this->entityManager->getRepository(Actividad::class)->find($id);
        if (!$actividad) {
            return $this->json(['message' => 'Actividad no encontrada.'], 404);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $status = $request->query->get('status');
        $userId = $request->query->get('user_id');

        $qb = $this->entityManager->getRepository(ActividadEntrega::class)->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->andWhere('e.actividad = :actividad')
            ->setParameter('actividad', $actividad);

        if ($status !== null && $status !== '') {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', strtoupper((string) $status));
        }

        if ($userId !== null) {
            $qb->andWhere('u.id = :user_id')
                ->setParameter('user_id', (int) $userId);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('e.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (ActividadEntrega $item) => $this->mapItem($item), $items),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ]);
    }

    #[Route('/{id}/submissions/me', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function mySubmissions(int $id, Request $request): JsonResponse
    {
        $actividad = $this->entityManager->getRepository(Actividad::class)->find($id);
        if (!$actividad) {
            return $this->json(['message' => 'Actividad no encontrada.'], 404);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $qb = $this->entityManager->getRepository(ActividadEntrega::class)->createQueryBuilder('e')
            ->andWhere('e.actividad = :actividad')
            ->andWhere('e.user = :user')
            ->setParameter('actividad', $actividad)
            ->setParameter('user', $user);

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('e.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (ActividadEntrega $item) => $this->mapItem($item), $items),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ]);
    }

    #[Route('/{id}/submissions', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function create(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $actividad = $this->entityManager->getRepository(Actividad::class)->find($id);
        if (!$actividad) {
            return $this->json(['message' => 'Actividad no encontrada.'], 404);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $now = new \DateTimeImmutable();
        if ($actividad->getDueAt() && $now > $actividad->getDueAt()) {
            return $this->json(['message' => 'La actividad ya no recibe entregas.'], 400);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new ActividadEntregaCreateRequest();
        $dto->file_id = $data['file_id'] ?? null;
        $dto->content = $data['content'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if (($dto->content === null || trim($dto->content) === '') && $dto->file_id === null) {
            return $this->json(['message' => 'Debe enviar contenido o archivo.'], 400);
        }

        $entrega = new ActividadEntrega($actividad, $user);
        $entrega->setContent($dto->content);

        if ($dto->file_id !== null) {
            $file = $this->entityManager->getRepository(FileObject::class)->find((int) $dto->file_id);
            if (!$file) {
                return $this->json(['message' => 'Archivo no encontrado.'], 400);
            }
            $entrega->setFile($file);
        }

        $this->entityManager->persist($entrega);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($entrega)], 201);
    }

    #[Route('/submissions/{id}', methods: ['PUT'])]
    #[Security("is_granted('ROLE_TEACHER') or is_granted('ROLE_ADMIN')")]
    public function grade(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $entrega = $this->entityManager->getRepository(ActividadEntrega::class)->find($id);
        if (!$entrega) {
            return $this->json(['message' => 'Entrega no encontrada.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new ActividadEntregaGradeRequest();
        $dto->grade = $data['grade'] ?? null;
        $dto->feedback = $data['feedback'] ?? null;
        $dto->status = $data['status'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->grade !== null) {
            $entrega->setGrade($dto->grade);
            $entrega->setGradedAt(new \DateTimeImmutable());
        }

        if ($dto->feedback !== null) {
            $entrega->setFeedback($dto->feedback);
        }

        if ($dto->status !== null) {
            $entrega->setStatus(strtoupper($dto->status));
        }

        $entrega->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($entrega)]);
    }

    private function mapItem(ActividadEntrega $entrega): array
    {
        $actividad = $entrega->getActividad();
        $user = $entrega->getUser();
        $file = $entrega->getFile();

        return [
            'id' => $entrega->getId(),
            'status' => $entrega->getStatus(),
            'grade' => $entrega->getGrade(),
            'feedback' => $entrega->getFeedback(),
            'content' => $entrega->getContent(),
            'submitted_at' => $entrega->getSubmittedAt()->format('Y-m-d H:i:s'),
            'graded_at' => $entrega->getGradedAt()?->format('Y-m-d H:i:s'),
            'actividad' => [
                'id' => $actividad->getId(),
                'curso_virtual_id' => $actividad->getCursoVirtual()->getId(),
            ],
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
            ],
            'file' => $file ? $this->mapFile($file) : null,
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
}
