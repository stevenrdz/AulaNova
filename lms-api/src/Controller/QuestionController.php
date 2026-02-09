<?php

namespace App\Controller;

use App\DTO\QuestionCreateRequest;
use App\DTO\QuestionUpdateRequest;
use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/assessments/questions')]
#[Security("is_granted('ROLE_TEACHER') or is_granted('ROLE_STUDENT')")]
class QuestionController extends ApiController
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
        $type = $request->query->get('type');

        $qb = $this->entityManager->getRepository(Question::class)->createQueryBuilder('q')
            ->leftJoin('q.quiz', 'quiz');

        if ($quizId !== null) {
            $qb->andWhere('quiz.id = :quiz_id')
                ->setParameter('quiz_id', (int) $quizId);
        }

        if ($type !== null && $type !== '') {
            $qb->andWhere('q.type = :type')
                ->setParameter('type', strtoupper((string) $type));
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(q.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->orderBy('q.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Question $item) => $this->mapItem($item), $items),
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
        $dto = new QuestionCreateRequest();
        $dto->quiz_id = (int) ($data['quiz_id'] ?? 0);
        $dto->type = strtoupper((string) ($data['type'] ?? ''));
        $dto->prompt = $data['prompt'] ?? '';
        $dto->options = $data['options'] ?? null;
        $dto->correct_option = $data['correct_option'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $quiz = $this->entityManager->getRepository(Quiz::class)->find($dto->quiz_id);
        if (!$quiz) {
            return $this->json(['message' => 'Quiz no encontrado.'], 400);
        }

        if ($dto->type === 'SINGLE') {
            if (!is_array($dto->options) || count($dto->options) < 2) {
                return $this->json(['message' => 'Opciones inválidas.'], 400);
            }
            if ($dto->correct_option === null || $dto->correct_option === '') {
                return $this->json(['message' => 'correct_option es requerido.'], 400);
            }
        } else {
            $dto->options = null;
            $dto->correct_option = null;
        }

        $item = new Question($quiz, $dto->type, $dto->prompt);
        $item->setOptions($dto->options)
            ->setCorrectOption($dto->correct_option);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_TEACHER')]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $item = $this->entityManager->getRepository(Question::class)->find($id);
        if (!$item) {
            return $this->json(['message' => 'Registro no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new QuestionUpdateRequest();
        $dto->quiz_id = $data['quiz_id'] ?? null;
        $dto->type = isset($data['type']) ? strtoupper((string) $data['type']) : null;
        $dto->prompt = $data['prompt'] ?? null;
        $dto->options = $data['options'] ?? null;
        $dto->correct_option = $data['correct_option'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->quiz_id !== null) {
            $quiz = $this->entityManager->getRepository(Quiz::class)->find($dto->quiz_id);
            if (!$quiz) {
                return $this->json(['message' => 'Quiz no encontrado.'], 400);
            }
            $item->setQuiz($quiz);
        }

        if ($dto->type !== null) {
            $item->setType($dto->type);
        }

        if ($dto->prompt !== null) {
            $item->setPrompt($dto->prompt);
        }

        if ($item->getType() === 'SINGLE') {
            if ($dto->options !== null) {
                if (!is_array($dto->options) || count($dto->options) < 2) {
                    return $this->json(['message' => 'Opciones inválidas.'], 400);
                }
                $item->setOptions($dto->options);
            }
            if ($dto->correct_option !== null) {
                if ($dto->correct_option === '') {
                    return $this->json(['message' => 'correct_option es requerido.'], 400);
                }
                $item->setCorrectOption($dto->correct_option);
            }
        } else {
            $item->setOptions(null)->setCorrectOption(null);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->mapItem($item)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_TEACHER')]
    public function delete(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Question::class)->find($id);
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

    private function mapItem(Question $item): array
    {
        $quiz = $item->getQuiz();
        $user = $this->getUser();
        $roles = method_exists($user, 'getRoles') ? $user->getRoles() : [];
        $isTeacher = in_array('ROLE_TEACHER', $roles, true) || in_array('ROLE_ADMIN', $roles, true);

        return [
            'id' => $item->getId(),
            'type' => $item->getType(),
            'prompt' => $item->getPrompt(),
            'options' => $item->getOptions(),
            'correct_option' => $isTeacher ? $item->getCorrectOption() : null,
            'quiz' => [
                'id' => $quiz->getId(),
                'curso_virtual_id' => $quiz->getCursoVirtual()->getId(),
            ],
        ];
    }
}

