<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findActiveByEmail($email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new \RuntimeException('Credenciales inválidas.');
        }

        return $this->buildTokens($user);
    }

    public function refresh(string $refreshToken): array
    {
        [$user, $newRefresh] = $this->refreshTokenService->rotate($refreshToken);
        $accessToken = $this->jwtManager->create($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefresh,
            'user' => $user,
        ];
    }

    public function buildTokens(User $user): array
    {
        $accessToken = $this->jwtManager->create($user);
        [$refreshRaw] = $this->refreshTokenService->issue($user);

        $this->entityManager->flush();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshRaw,
            'user' => $user,
        ];
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new \RuntimeException('Contraseña actual inválida.');
        }

        if ($currentPassword === $newPassword) {
            throw new \RuntimeException('La nueva contrase?a no puede ser igual a la actual.');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $this->refreshTokenService->revokeAllForUser($user);
        $this->entityManager->flush();
    }
}

