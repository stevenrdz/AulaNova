<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $roles = [
            'ROLE_ADMIN',
            'ROLE_TEACHER',
            'ROLE_STUDENT',
        ];

        $roleEntities = [];
        foreach ($roles as $roleName) {
            $role = new Role($roleName);
            $manager->persist($role);
            $roleEntities[$roleName] = $role;
        }

        $admin = new User();
        $admin->setEmail('admin@lms.local')
            ->setFirstName('Admin')
            ->setLastName('LMS')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'))
            ->addRoleEntity($roleEntities['ROLE_ADMIN']);

        $manager->persist($admin);
        $manager->flush();
    }
}
