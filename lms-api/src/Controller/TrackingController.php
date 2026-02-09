<?php

namespace App\Controller;

use App\DTO\TrackingHeartbeatRequest;
use App\Entity\Curso;
use App\Entity\TimeTrackingDaily;
use App\Entity\TimeTrackingRouteDaily;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/tracking')]
class TrackingController extends ApiController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/heartbeat', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function heartbeat(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new TrackingHeartbeatRequest();
        $dto->route = $data['route'] ?? '';
        $dto->course_id = $data['course_id'] ?? null;
        $dto->timestamp = (int) ($data['timestamp'] ?? 0);

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $timestamp = $dto->timestamp > 0 ? (int) ($dto->timestamp / 1000) : time();
        $day = (new \DateTimeImmutable())->setTimestamp($timestamp)->setTime(0, 0);

        $curso = null;
        if ($dto->course_id) {
            $curso = $this->entityManager->getRepository(Curso::class)->find($dto->course_id);
        }

        $repo = $this->entityManager->getRepository(TimeTrackingDaily::class);
        $existing = $repo->findOneBy([
            'user' => $user,
            'curso' => $curso,
            'day' => $day,
        ]);

        if (!$existing) {
            $existing = new TimeTrackingDaily($user, $day);
            if ($curso) {
                $existing->setCurso($curso);
            }
            $this->entityManager->persist($existing);
        }

        $existing->incrementSeconds(15);

        $route = trim($dto->route);
        if ($route !== '') {
            $routeRepo = $this->entityManager->getRepository(TimeTrackingRouteDaily::class);
            $routeTracking = $routeRepo->findOneBy([
                'user' => $user,
                'curso' => $curso,
                'day' => $day,
                'route' => $route,
            ]);

            if (!$routeTracking) {
                $routeTracking = new TimeTrackingRouteDaily($user, $day, $route);
                if ($curso) {
                    $routeTracking->setCurso($curso);
                }
                $this->entityManager->persist($routeTracking);
            }

            $routeTracking->incrementSeconds(15);
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'ok', 'seconds' => $existing->getSeconds()]);
    }

    #[Route('/summary', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function summary(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c.id AS curso_id, c.name AS curso_name, SUM(t.seconds) AS seconds')
            ->from(TimeTrackingDaily::class, 't')
            ->leftJoin('t.curso', 'c')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id, c.name');

        $rows = $qb->getQuery()->getArrayResult();
        $total = 0;
        $byCourse = array_map(function (array $row) use (&$total) {
            $seconds = (int) $row['seconds'];
            $total += $seconds;

            return [
                'curso_id' => $row['curso_id'],
                'curso_name' => $row['curso_name'],
                'seconds' => $seconds,
            ];
        }, $rows);

        return $this->json([
            'data' => [
                'total_seconds' => $total,
                'by_course' => $byCourse,
            ],
        ]);
    }

    #[Route('/admin/summary', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminSummary(Request $request): JsonResponse
    {
        $filters = $this->parseSummaryFilters($request);
        if ($filters instanceof JsonResponse) {
            return $filters;
        }

        $qb = $this->createSummaryQueryBuilder(
            $filters['from'],
            $filters['to'],
            $filters['course_id'],
            $filters['user_id']
        );

        $routeQb = $this->createRouteSummaryQueryBuilder(
            $filters['from'],
            $filters['to'],
            $filters['course_id'],
            $filters['user_id']
        );

        return $this->json(['data' => $this->buildSummaryResponse($qb, $routeQb)]);
    }

    #[Route('/admin/routes', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRoutes(Request $request): JsonResponse
    {
        $filters = $this->parseRouteFilters($request);
        if ($filters instanceof JsonResponse) {
            return $filters;
        }

        $routeQb = $this->createRouteSummaryQueryBuilder(
            $filters['from'],
            $filters['to'],
            $filters['course_id'],
            $filters['user_id'],
            null,
            $filters['route']
        );

        return $this->json(['data' => $this->buildRouteDailyResponse($routeQb)]);
    }

    #[Route('/teacher/summary', methods: ['GET'])]
    #[IsGranted('ROLE_TEACHER')]
    public function teacherSummary(Request $request): JsonResponse
    {
        $filters = $this->parseSummaryFilters($request);
        if ($filters instanceof JsonResponse) {
            return $filters;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $qb = $this->createSummaryQueryBuilder(
            $filters['from'],
            $filters['to'],
            $filters['course_id'],
            $filters['user_id'],
            $user
        );

        $routeQb = $this->createRouteSummaryQueryBuilder(
            $filters['from'],
            $filters['to'],
            $filters['course_id'],
            $filters['user_id'],
            $user
        );

        return $this->json(['data' => $this->buildSummaryResponse($qb, $routeQb, true)]);
    }

    #[Route('/teacher/routes', methods: ['GET'])]
    #[IsGranted('ROLE_TEACHER')]
    public function teacherRoutes(Request $request): JsonResponse
    {
        $filters = $this->parseRouteFilters($request);
        if ($filters instanceof JsonResponse) {
            return $filters;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $routeQb = $this->createRouteSummaryQueryBuilder(
            $filters['from'],
            $filters['to'],
            $filters['course_id'],
            $filters['user_id'],
            $user,
            $filters['route']
        );

        return $this->json(['data' => $this->buildRouteDailyResponse($routeQb, true)]);
    }

    private function parseRouteFilters(Request $request): array|JsonResponse
    {
        $filters = $this->parseSummaryFilters($request);
        if ($filters instanceof JsonResponse) {
            return $filters;
        }

        $route = trim((string) $request->query->get('route', ''));

        return [
            'from' => $filters['from'],
            'to' => $filters['to'],
            'course_id' => $filters['course_id'],
            'user_id' => $filters['user_id'],
            'route' => $route !== '' ? $route : null,
        ];
    }

    private function parseSummaryFilters(Request $request): array|JsonResponse
    {
        $from = $this->parseDate($request->query->get('from'));
        if ($request->query->has('from') && $from === null) {
            return $this->dateValidationError('from');
        }

        $to = $this->parseDate($request->query->get('to'));
        if ($request->query->has('to') && $to === null) {
            return $this->dateValidationError('to');
        }

        $courseId = $request->query->get('course_id');
        $userId = $request->query->get('user_id');

        return [
            'from' => $from,
            'to' => $to,
            'course_id' => $courseId !== null ? (int) $courseId : null,
            'user_id' => $userId !== null ? (int) $userId : null,
        ];
    }

    private function createSummaryQueryBuilder(
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        ?int $courseId,
        ?int $userId,
        ?User $teacher = null
    ) {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(TimeTrackingDaily::class, 't')
            ->leftJoin('t.curso', 'c')
            ->join('t.user', 'u');

        if ($from !== null) {
            $qb->andWhere('t.day >= :from')->setParameter('from', $from);
        }
        if ($to !== null) {
            $qb->andWhere('t.day <= :to')->setParameter('to', $to);
        }
        if ($courseId !== null) {
            $qb->andWhere('c.id = :course_id')->setParameter('course_id', $courseId);
        }
        if ($userId !== null) {
            $qb->andWhere('u.id = :user_id')->setParameter('user_id', $userId);
        }
        if ($teacher !== null) {
            $qb->andWhere('c.teacher = :teacher')->setParameter('teacher', $teacher);
        }

        return $qb;
    }

    private function createRouteSummaryQueryBuilder(
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        ?int $courseId,
        ?int $userId,
        ?User $teacher = null,
        ?string $route = null
    ) {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(TimeTrackingRouteDaily::class, 'tr')
            ->leftJoin('tr.curso', 'c')
            ->join('tr.user', 'u');

        if ($from !== null) {
            $qb->andWhere('tr.day >= :from')->setParameter('from', $from);
        }
        if ($to !== null) {
            $qb->andWhere('tr.day <= :to')->setParameter('to', $to);
        }
        if ($courseId !== null) {
            $qb->andWhere('c.id = :course_id')->setParameter('course_id', $courseId);
        }
        if ($userId !== null) {
            $qb->andWhere('u.id = :user_id')->setParameter('user_id', $userId);
        }
        if ($teacher !== null) {
            $qb->andWhere('c.teacher = :teacher')->setParameter('teacher', $teacher);
        }
        if ($route !== null) {
            $qb->andWhere('tr.route = :route')->setParameter('route', $route);
        }

        return $qb;
    }

    private function buildRouteDailyResponse($routeQb, bool $hideNullCourse = false): array
    {
        $total = (int) (clone $routeQb)
            ->select('COALESCE(SUM(tr.seconds), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        $byRouteQb = (clone $routeQb)
            ->select('tr.route AS route, COALESCE(SUM(tr.seconds), 0) AS seconds')
            ->groupBy('tr.route')
            ->orderBy('seconds', 'DESC');

        $byRouteDayQb = (clone $routeQb)
            ->select('tr.route AS route, tr.day AS day, COALESCE(SUM(tr.seconds), 0) AS seconds')
            ->groupBy('tr.route, tr.day')
            ->orderBy('tr.route', 'ASC')
            ->addOrderBy('tr.day', 'ASC');

        if ($hideNullCourse) {
            $byRouteQb->andWhere('c.id IS NOT NULL');
            $byRouteDayQb->andWhere('c.id IS NOT NULL');
        }

        $byRoute = [];
        foreach ($byRouteQb->getQuery()->getArrayResult() as $row) {
            $route = (string) $row['route'];
            $byRoute[$route] = [
                'route' => $route,
                'seconds' => (int) $row['seconds'],
                'by_day' => [],
            ];
        }

        foreach ($byRouteDayQb->getQuery()->getArrayResult() as $row) {
            $route = (string) $row['route'];
            if (!isset($byRoute[$route])) {
                $byRoute[$route] = [
                    'route' => $route,
                    'seconds' => 0,
                    'by_day' => [],
                ];
            }
            $day = $row['day'] instanceof \DateTimeImmutable ? $row['day']->format('Y-m-d') : (string) $row['day'];
            $byRoute[$route]['by_day'][] = [
                'day' => $day,
                'seconds' => (int) $row['seconds'],
            ];
        }

        return [
            'total_seconds' => $total,
            'by_route' => array_values($byRoute),
        ];
    }

    private function buildSummaryResponse($qb, $routeQb, bool $hideNullCourse = false): array
    {
        $total = (int) (clone $qb)
            ->select('COALESCE(SUM(t.seconds), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        $byCourseQb = (clone $qb)
            ->select('c.id AS curso_id, c.name AS curso_name, COALESCE(SUM(t.seconds), 0) AS seconds')
            ->groupBy('c.id, c.name')
            ->orderBy('seconds', 'DESC');

        if ($hideNullCourse) {
            $byCourseQb->andWhere('c.id IS NOT NULL');
        }

        $byCourse = array_map(static fn (array $row) => [
            'curso_id' => $row['curso_id'],
            'curso_name' => $row['curso_name'],
            'seconds' => (int) $row['seconds'],
        ], $byCourseQb->getQuery()->getArrayResult());

        $byUser = array_map(static fn (array $row) => [
            'user_id' => $row['user_id'],
            'email' => $row['email'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'seconds' => (int) $row['seconds'],
        ], (clone $qb)
            ->select('u.id AS user_id, u.email AS email, u.firstName AS first_name, u.lastName AS last_name, COALESCE(SUM(t.seconds), 0) AS seconds')
            ->groupBy('u.id, u.email, u.firstName, u.lastName')
            ->orderBy('seconds', 'DESC')
            ->getQuery()
            ->getArrayResult());

        $byDay = array_map(static fn (array $row) => [
            'day' => $row['day'] instanceof \DateTimeImmutable ? $row['day']->format('Y-m-d') : (string) $row['day'],
            'seconds' => (int) $row['seconds'],
        ], (clone $qb)
            ->select('t.day AS day, COALESCE(SUM(t.seconds), 0) AS seconds')
            ->groupBy('t.day')
            ->orderBy('t.day', 'ASC')
            ->getQuery()
            ->getArrayResult());

        $byRouteQb = (clone $routeQb)
            ->select('tr.route AS route, COALESCE(SUM(tr.seconds), 0) AS seconds')
            ->groupBy('tr.route')
            ->orderBy('seconds', 'DESC');

        if ($hideNullCourse) {
            $byRouteQb->andWhere('c.id IS NOT NULL');
        }

        $byRoute = array_map(static fn (array $row) => [
            'route' => $row['route'],
            'seconds' => (int) $row['seconds'],
        ], $byRouteQb->getQuery()->getArrayResult());

        return [
            'total_seconds' => $total,
            'by_course' => $byCourse,
            'by_user' => $byUser,
            'by_day' => $byDay,
            'by_route' => $byRoute,
        ];
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->setTime(0, 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function dateValidationError(string $field): JsonResponse
    {
        return $this->json([
            'message' => 'Validation failed',
            'errors' => [
                $field => ['Formato inválido. Usa una fecha válida.'],
            ],
        ], 422);
    }
}
