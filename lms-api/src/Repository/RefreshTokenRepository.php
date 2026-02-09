<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findActiveByHash(string $tokenHash): ?RefreshToken
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.tokenHash = :hash')
            ->andWhere('t.revokedAt IS NULL')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('hash', $tokenHash)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function revokeAllForUser(User $user): int
    {
        return $this->createQueryBuilder('t')
            ->update()
            ->set('t.revokedAt', ':now')
            ->set('t.replacedBy', ':replacedBy')
            ->andWhere('t.user = :user')
            ->andWhere('t.revokedAt IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('replacedBy', null)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
