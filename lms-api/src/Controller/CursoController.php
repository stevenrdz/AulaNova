<?php

namespace App\Controller;

use App\DTO\CursoCreateRequest;
use App\DTO\CursoUpdateRequest;
use App\Entity\Asignatura;
use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\Periodo;
use App\Entity\SedeJornada;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/structure/cursos')]
#[IsGranted('ROLE_ADMIN')]
class CursoController extends ApiController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    ) {
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
        $periodoId = $request->query->get('periodo_id');
        $teacherId = $request->query->get('teacher_id');
        $sedeJornadaId = $request->query->get('sede_jornada_id');
        $carreraId = $request->query->get('carrera_id');
        $asignaturaId = $request->query->get('asignatura_id');

        $qb = $this->entityManager->getRepository(Curso::class)->createQueryBuilder('c')
            ->leftJoin('c.periodo', 'p')
            ->leftJoin('c.teacher', 't')
            ->leftJoin('c.sedeJornada', 's')
            ->leftJoin('c.carrera', 'ca')
            ->leftJoin('c.asignatura', 'a');

        if ($q !== '') {
            $qb->andWhere('LOWER(c.name) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($periodoId !== null) {
            $qb->andWhere('p.id = :periodo_id')
                ->setParameter('periodo_id', (int) $periodoId);
        }

        if ($teacherId !== null) {
            $qb->andWhere('t.id = :teacher_id')
                ->setParameter('teacher_id', (int) $teacherId);
        }

        if ($sedeJornadaId !== null) {
            $qb->andWhere('s.id = :sede_jornada_id')
                ->setParameter('sede_jornada_id', (int) $sedeJornadaId);
        }

        if ($carreraId !== null) {
            $qb->andWhere('ca.id = :carrera_id')
                ->setParameter('carrera_id', (int) $carreraId);
        }

        if ($asignaturaId !== null) {
            $qb->andWhere('a.id = :asignatura_id')
                ->setParameter('asignatura_id', (int) $asignaturaId);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('c.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Curso $item) => $this->mapItem($item), $items),
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
        $dto = new CursoCreateRequest();
        $dto->name = $data['name'] ?? '';
        $dto->capacity = $data['capacity'] ?? null;
        $dto->start_date = $data['start_date'] ?? null;
        $dto->end_date = $data['end_date'] ?? null;
        $dto->periodo_id = $data['periodo_id'] ?? null;
        $dto->teacher_id = $data['teacher_id'] ?? null;
        $dto->sede_jornada_id = $data['sede_jornada_id'] ?? null;
        $dto->carrera_id = $data['carrera_id'] ?? null;
        $dto->asignatura_id = $data['asignatura_id'] ?? null;

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

        $item = new Curso();
        $item->setName($dto->name)
            ->setCapacity($dto->capacity)
            ->setStartDate($startDate)
            ->setEndDate($endDate);

        if ($dto->periodo_id !== null) {
            $periodo = $this->entityManager->getRepository(Periodo::class)->find($dto->periodo_id);
            if (!$periodo) {
                return $this->json(['message' => 'Periodo no encontrado.'], 400);
            }
            $item->setPeriodo($periodo);
        }

        if ($dto->teacher_id !== null) {
            $teacher = $this->userRepository->find($dto->teacher_id);
            if (!$teacher) {
                return $this->json(['message' => 'Docente no encontrado.'], 400);
            }
            if (!in_array('ROLE_TEACHER', $teacher->getRoles(), true)) {
                return $this->json(['message' => 'El usuario no es docente.'], 400);
            }
            $item->setTeacher($teacher);
        }

        if ($dto->sede_jornada_id !== null) {
            $sedeJornada = $this->entityManager->getRepository(SedeJornada::class)->find($dto->sede_jornada_id);
            if (!$sedeJornada) {
                return $this->json(['message' => 'Sede jornada no encontrada.'], 400);
            }
            $item->setSedeJornada($sedeJornada);
        }

        if ($dto->carrera_id !== null) {
            $carrera = $this->entityManager->getRepository(Carrera::class)->find($dto->carrera_id);
            if (!$carrera) {
                return $this->json(['message' => 'Carrera no encontrada.'], 400);
            }
            $item->setCarrera($carrera);
        }

        if ($dto->asignatura_id !== null) {
            $asignatura = $this->entityManager->getRepository(Asignatura::class)->find($dto->asignatura_id);
            if (!$asignatura) {
                return $this->json(['message' => 'Asignatura no encontrada.'], 400);
            }
            $item->setAsignatura($asignatura);
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new CursoUpdateRequest();
        $dto->name = $data['name'] ?? null;
        $dto->capacity = $data['capacity'] ?? null;
        $dto->start_date = $data['start_date'] ?? null;
        $dto->end_date = $data['end_date'] ?? null;
        $dto->periodo_id = $data['periodo_id'] ?? null;
        $dto->teacher_id = $data['teacher_id'] ?? null;
        $dto->sede_jornada_id = $data['sede_jornada_id'] ?? null;
        $dto->carrera_id = $data['carrera_id'] ?? null;
        $dto->asignatura_id = $data['asignatura_id'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->name !== null) {
            $item->setName($dto->name);
        }
        if ($dto->capacity !== null) {
            $item->setCapacity($dto->capacity);
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

        if ($dto->periodo_id !== null) {
            $periodo = $this->entityManager->getRepository(Periodo::class)->find($dto->periodo_id);
            if (!$periodo) {
                return $this->json(['message' => 'Periodo no encontrado.'], 400);
            }
            $item->setPeriodo($periodo);
        }

        if ($dto->teacher_id !== null) {
            $teacher = $this->userRepository->find($dto->teacher_id);
            if (!$teacher) {
                return $this->json(['message' => 'Docente no encontrado.'], 400);
            }
            if (!in_array('ROLE_TEACHER', $teacher->getRoles(), true)) {
                return $this->json(['message' => 'El usuario no es docente.'], 400);
            }
            $item->setTeacher($teacher);
        }

        if ($dto->sede_jornada_id !== null) {
            $sedeJornada = $this->entityManager->getRepository(SedeJornada::class)->find($dto->sede_jornada_id);
            if (!$sedeJornada) {
                return $this->json(['message' => 'Sede jornada no encontrada.'], 400);
            }
            $item->setSedeJornada($sedeJornada);
        }

        if ($dto->carrera_id !== null) {
            $carrera = $this->entityManager->getRepository(Carrera::class)->find($dto->carrera_id);
            if (!$carrera) {
                return $this->json(['message' => 'Carrera no encontrada.'], 400);
            }
            $item->setCarrera($carrera);
        }

        if ($dto->asignatura_id !== null) {
            $asignatura = $this->entityManager->getRepository(Asignatura::class)->find($dto->asignatura_id);
            if (!$asignatura) {
                return $this->json(['message' => 'Asignatura no encontrada.'], 400);
            }
            $item->setAsignatura($asignatura);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Curso::class)->find($id);
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

    private function mapItem(Curso $item): array
    {
        $teacher = $item->getTeacher();
        $periodo = $item->getPeriodo();
        $sedeJornada = $item->getSedeJornada();
        $carrera = $item->getCarrera();
        $asignatura = $item->getAsignatura();

        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'capacity' => $item->getCapacity(),
            'start_date' => $item->getStartDate()?->format('Y-m-d'),
            'end_date' => $item->getEndDate()?->format('Y-m-d'),
            'periodo' => $periodo ? [
                'id' => $periodo->getId(),
                'name' => $periodo->getName(),
            ] : null,
            'teacher' => $teacher ? [
                'id' => $teacher->getId(),
                'email' => $teacher->getEmail(),
                'first_name' => $teacher->getFirstName(),
                'last_name' => $teacher->getLastName(),
            ] : null,
            'sede_jornada' => $sedeJornada ? [
                'id' => $sedeJornada->getId(),
                'name' => $sedeJornada->getName(),
            ] : null,
            'carrera' => $carrera ? [
                'id' => $carrera->getId(),
                'name' => $carrera->getName(),
            ] : null,
            'asignatura' => $asignatura ? [
                'id' => $asignatura->getId(),
                'name' => $asignatura->getName(),
            ] : null,
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
                $field => ['Formato inv?lido. Usa YYYY-MM-DD.'],
            ],
        ], 422);
    }
}

