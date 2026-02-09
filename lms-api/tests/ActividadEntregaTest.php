<?php

namespace App\Tests;

use App\Entity\Actividad;
use App\Entity\Curso;
use App\Entity\CursoVirtual;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ActividadEntregaTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserPasswordHasherInterface $passwordHasher = null;
    private array $createdEntities = [];
    private $client;
    private ?int $actividadId = null;

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

    public function testSubmissionsFlow(): void
    {
        $teacherToken = $this->loginAndGetToken($this->client, $this->getUserEmail('teacher'), $this->getUserPassword('teacher'));
        $studentToken = $this->loginAndGetToken($this->client, $this->getUserEmail('student'), $this->getUserPassword('student'));

        $this->client->request('POST', sprintf('/virtual/actividades/%d/submissions', $this->actividadId), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $studentToken,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['content' => 'Entrega test']));
        $this->assertResponseStatusCodeSame(201);
        $submission = json_decode($this->client->getResponse()->getContent(), true);
        $submissionId = $submission['data']['id'] ?? null;
        $this->assertNotNull($submissionId);

        $this->client->request('GET', sprintf('/virtual/actividades/%d/submissions', $this->actividadId), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $teacherToken,
        ]);
        $this->assertResponseIsSuccessful();

        $this->client->request('PUT', sprintf('/virtual/actividades/submissions/%d', $submissionId), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $teacherToken,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['grade' => 95, 'feedback' => 'OK', 'status' => 'GRADED']));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', sprintf('/virtual/actividades/%d/submissions/me', $this->actividadId), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $studentToken,
        ]);
        $this->assertResponseIsSuccessful();
        $my = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $my['data']);
        $this->assertSame('GRADED', $my['data'][0]['status']);
    }

    private function seedData(): void
    {
        $teacherRole = $this->getOrCreateRole('ROLE_TEACHER');
        $studentRole = $this->getOrCreateRole('ROLE_STUDENT');

        $teacher = $this->createUser('teacher', $teacherRole);
        $student = $this->createUser('student', $studentRole);

        $curso = new Curso();
        $curso->setName('Curso Test')
            ->setTeacher($teacher);
        $this->entityManager->persist($curso);
        $this->createdEntities[] = $curso;
        $this->entityManager->flush();

        $cursoVirtual = new CursoVirtual($curso);
        $cursoVirtual->setDescription('Curso virtual test');
        $this->entityManager->persist($cursoVirtual);
        $this->createdEntities[] = $cursoVirtual;

        $actividad = new Actividad($cursoVirtual, 'TEXT', 'Actividad Test');
        $actividad->setContent('Entrega');
        $this->entityManager->persist($actividad);
        $this->createdEntities[] = $actividad;

        $this->entityManager->flush();

        $this->actividadId = $actividad->getId();
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
