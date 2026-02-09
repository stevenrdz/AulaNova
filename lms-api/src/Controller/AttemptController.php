<?php

namespace App\Controller;

use App\DTO\AttemptCreateRequest;
use App\Entity\Answer;
use App\Entity\Attempt;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/assessments/attempts')]
#[IsGranted('ROLE_STUDENT')]
class AttemptController extends ApiController
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

        $quizId = $request->query->get('quiz_id');
        $userId = $request->query->get('user_id');

        $qb = $this->entityManager->getRepository(Attempt::class)->createQueryBuilder('a')
            ->leftJoin('a.quiz', 'qz')
            ->leftJoin('a.user', 'u');

        $user = $this->getUser();
        if ($user instanceof User) {
            $roles = $user->getRoles();
            $isTeacher = in_array('ROLE_TEACHER', $roles, true) || in_array('ROLE_ADMIN', $roles, true);
            if (!$isTeacher) {
                $qb->andWhere('u.id = :user_id')
                    ->setParameter('user_id', $user->getId());
            } elseif ($userId !== null) {
                $qb->andWhere('u.id = :user_id')
                    ->setParameter('user_id', (int) $userId);
            }
        }

        if ($quizId !== null) {
            $qb->andWhere('qz.id = :quiz_id')
                ->setParameter('quiz_id', (int) $quizId);
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
            'data' => array_map(fn (Attempt $item) => $this->mapItem($item), $items),
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
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AttemptCreateRequest();
        $dto->quiz_id = (int) ($data['quiz_id'] ?? 0);

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $quiz = $this->entityManager->getRepository(Quiz::class)->find($dto->quiz_id);
        if (!$quiz) {
            return $this->json(['message' => 'Quiz no encontrado.'], 400);
        }

        $now = new \DateTimeImmutable();
        if ($quiz->getStartAt() && $now < $quiz->getStartAt()) {
            return $this->json(['message' => 'El quiz a?n no est? disponible.'], 400);
        }
        if ($quiz->getEndAt() && $now > $quiz->getEndAt()) {
            return $this->json(['message' => 'El quiz ya no est? disponible.'], 400);
        }

        $item = new Attempt($quiz, $user);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}/finish', methods: ['POST'])]
    public function finish(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Attempt::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $roles = $user->getRoles();
            $isTeacher = in_array('ROLE_TEACHER', $roles, true) || in_array('ROLE_ADMIN', $roles, true);
            if (!$isTeacher && $item->getUser()->getId() !== $user->getId()) {
                return $this->json(['message' => 'Unauthorized'], 401);
            }
        }

        if ($item->getFinishedAt()) {
            return $this->json(['message' => 'El intento ya fue finalizado.'], 400);
        }

        $quiz = $item->getQuiz();
        $now = new \DateTimeImmutable();
        if ($quiz->getEndAt() && $now > $quiz->getEndAt()) {
            $item->setFinishedAt($quiz->getEndAt());
        } else {
            $item->setFinishedAt($now);
        }

        $score = $this->calculateScore($item);
        $item->setScore($score);

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Attempt::class)->find($id);
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

    private function mapItem(Attempt $item): array
    {
        $quiz = $item->getQuiz();
        $user = $item->getUser();

        return [
            'id' => $item->getId(),
            'started_at' => $item->getStartedAt()->format('Y-m-d H:i:s'),
            'finished_at' => $item->getFinishedAt()?->format('Y-m-d H:i:s'),
            'score' => $item->getScore(),
            'quiz' => [
                'id' => $quiz->getId(),
                'title' => $quiz->getTitle(),
            ],
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
            ],
        ];
    }

    private function calculateScore(Attempt $attempt): ?float
    {
        $answers = $this->entityManager->getRepository(Answer::class)
            ->findBy(['attempt' => $attempt]);

        $questions = $this->entityManager->getRepository(Question::class)
            ->findBy(['quiz' => $attempt->getQuiz(), 'type' => 'SINGLE']);

        $total = count($questions);
        if ($total === 0) {
            return null;
        }

        $correct = 0;
        foreach ($answers as $answer) {
            if ($answer->getIsCorrect()) {
                $correct++;
            }
        }

        return round(($correct / $total) * 100, 2);
    }
}

