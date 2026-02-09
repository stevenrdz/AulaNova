<?php

namespace App\Tests;

use App\Entity\Curso;
use App\Entity\Role;
use App\Entity\TimeTrackingDaily;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TrackingSummaryTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserPasswordHasherInterface $passwordHasher = null;
    private array $createdEntities = [];
    private $client;
    private ?int $courseId = null;
    private ?int $studentId = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->seedData();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
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

    public function testAuthLoginReturnsTokens(): void
    {
        $this->client->request('POST', '/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $this->getUserEmail('admin'),
            'password' => $this->getUserPassword('admin'),
        ]));

        $this->assertResponseIsSuccessful();
        $payload = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $payload);
        $this->assertArrayHasKey('refresh_token', $payload);
    }

    public function testTrackingSummaries(): void
    {
        $adminToken = $this->loginAndGetToken($this->client, $this->getUserEmail('admin'), $this->getUserPassword('admin'));
        $teacherToken = $this->loginAndGetToken($this->client, $this->getUserEmail('teacher'), $this->getUserPassword('teacher'));
        $studentToken = $this->loginAndGetToken($this->client, $this->getUserEmail('student'), $this->getUserPassword('student'));

        $this->client->request('GET', sprintf('/tracking/admin/summary?user_id=%d&course_id=%d', $this->studentId, $this->courseId), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $adminToken,
        ]);
        $this->assertResponseIsSuccessful();
        $adminPayload = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(120, $adminPayload['data']['total_seconds']);

        $this->client->request('GET', sprintf('/tracking/teacher/summary?course_id=%d', $this->courseId), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $teacherToken,
        ]);
        $this->assertResponseIsSuccessful();
        $teacherPayload = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(120, $teacherPayload['data']['total_seconds']);

        $this->client->request('GET', '/tracking/summary', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $studentToken,
        ]);
        $this->assertResponseIsSuccessful();
        $studentPayload = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(120, $studentPayload['data']['total_seconds']);
    }

    public function testAdminSummaryValidatesDates(): void
    {
        $adminToken = $this->loginAndGetToken($this->client, $this->getUserEmail('admin'), $this->getUserPassword('admin'));

        $this->client->request('GET', '/tracking/admin/summary?from=invalid-date', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $adminToken,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    private function seedData(): void
    {
        $adminRole = $this->getOrCreateRole('ROLE_ADMIN');
        $teacherRole = $this->getOrCreateRole('ROLE_TEACHER');
        $studentRole = $this->getOrCreateRole('ROLE_STUDENT');

        $admin = $this->createUser('admin', $adminRole);
        $teacher = $this->createUser('teacher', $teacherRole);
        $student = $this->createUser('student', $studentRole);

        $curso = new Curso();
        $curso->setName('Curso Test')
            ->setTeacher($teacher);
        $this->entityManager->persist($curso);
        $this->createdEntities[] = $curso;
        $this->entityManager->flush();
        $this->courseId = $curso->getId();
        $this->studentId = $student->getId();

        $day1 = new \DateTimeImmutable('2026-02-01');
        $day2 = new \DateTimeImmutable('2026-02-02');

        $t1 = new TimeTrackingDaily($student, $day1);
        $t1->setCurso($curso);
        $t1->incrementSeconds(60);

        $t2 = new TimeTrackingDaily($student, $day2);
        $t2->setCurso($curso);
        $t2->incrementSeconds(60);

        $this->entityManager->persist($t1);
        $this->entityManager->persist($t2);
        $this->createdEntities[] = $t1;
        $this->createdEntities[] = $t2;

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

    private function createUser(string $prefix, Role $role): User
    {
        $user = new User();
        $email = sprintf('%s_%s@lms.local', $prefix, bin2hex(random_bytes(4)));
        $password = $this->getUserPassword($prefix);

        $user->setEmail($email)
            ->setFirstName(ucfirst($prefix))
            ->setLastName('Test')
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
            ->addRoleEntity($role);

        $this->entityManager->persist($user);
        $this->createdEntities[] = $user;

        return $user;
    }

    private function getUserEmail(string $prefix): string
    {
        foreach ($this->createdEntities as $entity) {
            if ($entity instanceof User && str_starts_with($entity->getEmail(), $prefix . '_')) {
                return $entity->getEmail();
            }
        }

        throw new \RuntimeException('Test user not found for prefix: ' . $prefix);
    }

    private function getUserPassword(string $prefix): string
    {
        return ucfirst($prefix) . '123!';
    }

    private function loginAndGetToken($client, string $email, string $password): string
    {
        $client->request('POST', '/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $this->assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);

        return $payload['access_token'];
    }
}
