<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RefreshTokenService
{
    private int $refreshTtl;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        #[Autowire('%app.refresh_ttl%')] int $refreshTtl
    ) {
        $this->refreshTtl = $refreshTtl;
    }

    public function issue(User $user): array
    {
        $raw = bin2hex(random_bytes(32));
        $hash = $this->hashToken($raw);
        $expiresAt = (new \DateTimeImmutable())->modify('+' . $this->refreshTtl . ' seconds');

        $entity = new RefreshToken($user, $hash, $expiresAt);
        $this->entityManager->persist($entity);

        return [$raw, $hash, $entity];
    }

    public function rotate(string $rawToken): array
    {
        $hash = $this->hashToken($rawToken);
        $existing = $this->refreshTokenRepository->findOneBy(['tokenHash' => $hash]);

        if (!$existing) {
            throw new \RuntimeException('Refresh token inv?lido o expirado.');
        }

        if ($existing->getRevokedAt() !== null) {
            $this->refreshTokenRepository->revokeAllForUser($existing->getUser());
            $this->entityManager->flush();
            throw new \RuntimeException('Refresh token reutilizado. Sesi?n invalidada.');
        }

        if ($existing->getExpiresAt() <= new \DateTimeImmutable()) {
            throw new \RuntimeException('Refresh token inv?lido o expirado.');
        }

        [$newRaw, $newHash, $newEntity] = $this->issue($existing->getUser());
        $existing->revoke($newHash);

        $this->entityManager->flush();

        return [$existing->getUser(), $newRaw];
    }

    public function revoke(string $rawToken): void
    {
        $hash = $this->hashToken($rawToken);
        $existing = $this->refreshTokenRepository->findOneBy(['tokenHash' => $hash]);

        if ($existing) {
            $existing->revoke();
            $this->entityManager->flush();
        }
    }

    public function revokeAllForUser(User $user): void
    {
        $this->refreshTokenRepository->revokeAllForUser($user);
        $this->entityManager->flush();
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}

