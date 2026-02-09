<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findValidToken(User $user, string $otpHash): ?PasswordResetToken
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.usedAt IS NULL')
            ->andWhere('t.expiresAt > :now')
            ->andWhere('t.otpHash = :hash')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('hash', $otpHash)
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
