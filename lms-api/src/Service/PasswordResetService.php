<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetService
{
    private int $otpTtlSeconds = 900;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordResetTokenRepository $passwordResetTokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly MailerInterface $mailer
    ) {
    }

    public function requestReset(User $user): void
    {
        $otp = (string) random_int(100000, 999999);
        $hash = $this->hashOtp($otp);
        $expiresAt = (new \DateTimeImmutable())->modify('+' . $this->otpTtlSeconds . ' seconds');

        $token = new PasswordResetToken($user, $hash, $expiresAt);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $email = (new Email())
            ->from('no-reply@lms.local')
            ->to($user->getEmail())
            ->subject('C?digo de recuperaci?n')
            ->text('Tu c?digo OTP es: ' . $otp . ' (v?lido por 15 minutos).');

        $this->mailer->send($email);
    }

    public function resetPassword(User $user, string $otp, string $newPassword): void
    {
        $hash = $this->hashOtp($otp);
        $token = $this->passwordResetTokenRepository->findValidToken($user, $hash);

        if (!$token) {
            throw new \RuntimeException('OTP inv?lido o expirado.');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $token->markUsed();
        $this->refreshTokenService->revokeAllForUser($user);

        $this->entityManager->flush();
    }

    private function hashOtp(string $otp): string
    {
        return hash('sha256', $otp);
    }
}

