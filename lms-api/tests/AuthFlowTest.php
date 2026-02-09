<?php

namespace App\Tests;

use App\Entity\AuditLog;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthFlowTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserPasswordHasherInterface $passwordHasher = null;
    private array $createdEntities = [];
    private $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->seedUser();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
            $userIds = [];
            foreach ($this->createdEntities as $entity) {
                if ($entity instanceof User && $entity->getId() !== null) {
                    $userIds[] = $entity->getId();
                }
            }
            if ($userIds !== []) {
                $this->entityManager->createQueryBuilder()
                    ->delete(AuditLog::class, 'a')
                    ->where('a.user IN (:userIds)')
                    ->setParameter('userIds', $userIds)
                    ->getQuery()
                    ->execute();
            }

            foreach (array_reverse($this->createdEntities) as $entity) {
                $managed = $this->entityManager->getRepository(get_class($entity))->find($entity->getId());
                if ($managed) {
                    $this->entityManager->remove($managed);
                }
            }
            $this->entityManager->flush();
            $this->entityManager->close();
        }

        $this->entityManager = null;
        $this->passwordHasher = null;
        $this->createdEntities = [];
        $this->client = null;

        parent::tearDown();
    }

    public function testLoginRefreshLogoutFlow(): void
    {
        $user = $this->getSeededUser();
        $payload = $this->loginAndGetPayload($user->getEmail(), $this->getPassword());

        $this->assertArrayHasKey('access_token', $payload);
        $this->assertArrayHasKey('refresh_token', $payload);
        $this->assertSame(true, $payload['user']['is_active']);

        $this->client->request('POST', '/auth/refresh', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'refresh_token' => $payload['refresh_token'],
        ]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', '/auth/logout', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'refresh_token' => $payload['refresh_token'],
        ]));
        $this->assertResponseIsSuccessful();
    }

    public function testChangePassword(): void
    {
        $user = $this->getSeededUser();
        $payload = $this->loginAndGetPayload($user->getEmail(), $this->getPassword());

        $this->client->request('POST', '/auth/change-password', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $payload['access_token'],
        ], json_encode([
            'current_password' => $this->getPassword(),
            'new_password' => 'NewPass123!',
        ]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', '/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $user->getEmail(),
            'password' => 'NewPass123!',
        ]));
        $this->assertResponseIsSuccessful();
    }

    public function testAuthValidationErrors(): void
    {
        $this->client->request('POST', '/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(422);

        $this->client->request('POST', '/auth/logout', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(422);
    }

    private function seedUser(): void
    {
        $role = $this->getOrCreateRole('ROLE_ADMIN');
        $user = new User();
        $email = sprintf('auth_%s@lms.local', bin2hex(random_bytes(4)));
        $password = $this->getPassword();

        $user->setEmail($email)
            ->setFirstName('Auth')
            ->setLastName('Test')
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
            ->setIsActive(true)
            ->addRoleEntity($role);

        $this->entityManager->persist($user);
        $this->createdEntities[] = $user;
        $this->entityManager->flush();
    }

    private function getOrCreateRole(string $name): Role
    {
        $repo = $this->entityManager->getRepository(Role::class);
        $existing = $repo->findOneBy(['name' => $name]);
        if ($existing instanceof Role) {
            return $existing;
        }

        $role = new Role($name);
        $this->entityManager->persist($role);
        $this->createdEntities[] = $role;

        return $role;
    }

    private function getSeededUser(): User
    {
        foreach ($this->createdEntities as $entity) {
            if ($entity instanceof User && str_starts_with($entity->getEmail(), 'auth_')) {
                return $entity;
            }
        }

        throw new \RuntimeException('Seeded user not found.');
    }

    private function getPassword(): string
    {
        return 'Auth123!';
    }

    private function loginAndGetPayload(string $email, string $password): array
    {
        $this->client->request('POST', '/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $this->assertResponseIsSuccessful();

        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
