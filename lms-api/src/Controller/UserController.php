<?php

namespace App\Controller;

use App\DTO\UserCreateRequest;
use App\DTO\UserUpdateRequest;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuditLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends ApiController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuditLogService $auditLogService
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        return $this->listUsers($request, $request->query->get('role', 'ROLE_STUDENT'));
    }

    #[Route('/teachers', methods: ['GET'])]
    public function teachers(Request $request): JsonResponse
    {
        return $this->listUsers($request, 'ROLE_TEACHER');
    }

    #[Route('/admins', methods: ['GET'])]
    public function admins(Request $request): JsonResponse
    {
        return $this->listUsers($request, 'ROLE_ADMIN');
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new UserCreateRequest();
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->first_name = $data['first_name'] ?? '';
        $dto->last_name = $data['last_name'] ?? '';
        $dto->role = $data['role'] ?? 'ROLE_STUDENT';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($this->userRepository->findActiveByEmail($dto->email)) {
            return $this->json(['message' => 'El email ya existe.'], 409);
        }

        $user = new User();
        $user->setEmail($dto->email)
            ->setFirstName($dto->first_name)
            ->setLastName($dto->last_name)
            ->setPassword($this->passwordHasher->hashPassword($user, $dto->password));

        $roleEntity = $this->getOrCreateRole($dto->role);
        $user->addRoleEntity($roleEntity);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->auditLogService->log('create', 'user', (string) $user->getId());

        return $this->json(['data' => $this->mapUser($user)], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user || $user->getDeletedAt()) {
            return $this->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new UserUpdateRequest();
        $dto->email = $data['email'] ?? null;
        $dto->password = $data['password'] ?? null;
        $dto->first_name = $data['first_name'] ?? null;
        $dto->last_name = $data['last_name'] ?? null;
        $dto->role = $data['role'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        if ($dto->email) {
            $user->setEmail($dto->email);
        }
        if ($dto->first_name) {
            $user->setFirstName($dto->first_name);
        }
        if ($dto->last_name) {
            $user->setLastName($dto->last_name);
        }
        if ($dto->password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
        }
        if ($dto->role) {
            $user->getRoleEntities()->clear();
            $user->addRoleEntity($this->getOrCreateRole($dto->role));
        }

        $this->entityManager->flush();
        $this->auditLogService->log('update', 'user', (string) $user->getId());

        return $this->json(['data' => $this->mapUser($user)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user || $user->getDeletedAt()) {
            return $this->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->setDeletedAt(new \DateTimeImmutable());
        $user->setIsActive(false);
        $this->entityManager->flush();

        $this->auditLogService->log('delete', 'user', (string) $user->getId());

        return $this->json(['message' => 'Usuario eliminado']);
    }

    private function mapUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'is_active' => $user->isActive(),
        ];
    }

    private function getOrCreateRole(string $roleName): Role
    {
        $roleName = strtoupper($roleName);
        $repo = $this->entityManager->getRepository(Role::class);
        $role = $repo->findOneBy(['name' => $roleName]);

        if (!$role) {
            $role = new Role($roleName);
            $this->entityManager->persist($role);
            $this->entityManager->flush();
        }

        return $role;
    }

    private function listUsers(Request $request, ?string $role = null): JsonResponse
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

        $qb = $this->userRepository->createQueryBuilder('u')
            ->leftJoin('u.roleEntities', 'r')
            ->andWhere('u.deletedAt IS NULL');

        if ($role) {
            $qb->andWhere('r.name = :role')
                ->setParameter('role', strtoupper($role));
        }

        if ($q !== '') {
            $qb->andWhere('LOWER(u.email) LIKE :q OR LOWER(u.firstName) LIKE :q OR LOWER(u.lastName) LIKE :q')
                ->setParameter('q', '%' . strtolower($q) . '%');
        }

        if ($isActive !== null) {
            $qb->andWhere('u.isActive = :active')
                ->setParameter('active', $isActive);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(DISTINCT u.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->select('DISTINCT u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $users = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (User $user) => $this->mapUser($user), $users),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ]);
    }
}
