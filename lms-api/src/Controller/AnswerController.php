<?php

namespace App\Controller;

use App\DTO\AnswerCreateRequest;
use App\DTO\AnswerUpdateRequest;
use App\Entity\Answer;
use App\Entity\Attempt;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/assessments/answers')]
#[IsGranted('ROLE_STUDENT')]
class AnswerController extends ApiController
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

        $attemptId = $request->query->get('attempt_id');

        $qb = $this->entityManager->getRepository(Answer::class)->createQueryBuilder('a')
            ->leftJoin('a.attempt', 'att')
            ->leftJoin('a.question', 'q');

        $user = $this->getUser();
        if ($user instanceof User) {
            $roles = $user->getRoles();
            $isTeacher = in_array('ROLE_TEACHER', $roles, true) || in_array('ROLE_ADMIN', $roles, true);
            if (!$isTeacher) {
                $qb->leftJoin('att.user', 'u')
                    ->andWhere('u.id = :user_id')
                    ->setParameter('user_id', $user->getId());
            }
        }

        if ($attemptId !== null) {
            $qb->andWhere('att.id = :attempt_id')
                ->setParameter('attempt_id', (int) $attemptId);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('a.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Answer $item) => $this->mapItem($item), $items),
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
        $dto = new AnswerCreateRequest();
        $dto->attempt_id = (int) ($data['attempt_id'] ?? 0);
        $dto->question_id = (int) ($data['question_id'] ?? 0);
        $dto->answer_text = $data['answer_text'] ?? null;
        $dto->is_correct = $data['is_correct'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $attempt = $this->entityManager->getRepository(Attempt::class)->find($dto->attempt_id);
        if (!$attempt) {
            return $this->json(['message' => 'Intento no encontrado.'], 400);
        }

        $question = $this->entityManager->getRepository(Question::class)->find($dto->question_id);
        if (!$question) {
            return $this->json(['message' => 'Pregunta no encontrada.'], 400);
        }

        if ($question->getQuiz()->getId() !== $attempt->getQuiz()->getId()) {
            return $this->json(['message' => 'La pregunta no pertenece al quiz.'], 400);
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $roles = $user->getRoles();
            $isTeacher = in_array('ROLE_TEACHER', $roles, true) || in_array('ROLE_ADMIN', $roles, true);
            if (!$isTeacher && $attempt->getUser()->getId() !== $user->getId()) {
                return $this->json(['message' => 'Unauthorized'], 401);
            }
            if (!$isTeacher) {
                $dto->is_correct = null;
            }
        }

        if ($attempt->getFinishedAt()) {
            return $this->json(['message' => 'El intento ya fue finalizado.'], 400);
        }

        $item = new Answer($attempt, $question);
        $item->setAnswerText($dto->answer_text);

        if ($question->getType() === 'SINGLE') {
            $correct = $question->getCorrectOption();
            if ($correct !== null && $dto->answer_text !== null) {
                $item->setIsCorrect($dto->answer_text === $correct);
            }
        } elseif ($dto->is_correct !== null) {
            $item->setIsCorrect($dto->is_correct);
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Answer::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AnswerUpdateRequest();
        $dto->attempt_id = $data['attempt_id'] ?? null;
        $dto->question_id = $data['question_id'] ?? null;
        $dto->answer_text = $data['answer_text'] ?? null;
        $dto->is_correct = $data['is_correct'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $attempt = $item->getAttempt();
        $question = $item->getQuestion();

        if ($dto->attempt_id !== null) {
            $attempt = $this->entityManager->getRepository(Attempt::class)->find($dto->attempt_id);
            if (!$attempt) {
                return $this->json(['message' => 'Intento no encontrado.'], 400);
            }
            $item->setAttempt($attempt);
        }

        if ($dto->question_id !== null) {
            $question = $this->entityManager->getRepository(Question::class)->find($dto->question_id);
            if (!$question) {
                return $this->json(['message' => 'Pregunta no encontrada.'], 400);
            }
            $item->setQuestion($question);
        }

        if ($question->getQuiz()->getId() !== $attempt->getQuiz()->getId()) {
            return $this->json(['message' => 'La pregunta no pertenece al quiz.'], 400);
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $roles = $user->getRoles();
            $isTeacher = in_array('ROLE_TEACHER', $roles, true) || in_array('ROLE_ADMIN', $roles, true);
            if (!$isTeacher && $attempt->getUser()->getId() !== $user->getId()) {
                return $this->json(['message' => 'Unauthorized'], 401);
            }
            if (!$isTeacher) {
                $dto->is_correct = null;
            }
        }

        if ($attempt->getFinishedAt()) {
            return $this->json(['message' => 'El intento ya fue finalizado.'], 400);
        }

        if ($dto->answer_text !== null) {
            $item->setAnswerText($dto->answer_text);
        }

        if ($question->getType() === 'SINGLE') {
            $correct = $question->getCorrectOption();
            if ($correct !== null && $item->getAnswerText() !== null) {
                $item->setIsCorrect($item->getAnswerText() === $correct);
            }
        } elseif ($dto->is_correct !== null) {
            $item->setIsCorrect($dto->is_correct);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Answer::class)->find($id);
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

    private function mapItem(Answer $item): array
    {
        $attempt = $item->getAttempt();
        $question = $item->getQuestion();

        return [
            'id' => $item->getId(),
            'answer_text' => $item->getAnswerText(),
            'is_correct' => $item->getIsCorrect(),
            'attempt' => [
                'id' => $attempt->getId(),
                'quiz_id' => $attempt->getQuiz()->getId(),
                'user_id' => $attempt->getUser()->getId(),
            ],
            'question' => [
                'id' => $question->getId(),
                'type' => $question->getType(),
                'prompt' => $question->getPrompt(),
            ],
        ];
    }
}

