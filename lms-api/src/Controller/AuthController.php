<?php

namespace App\Controller;

use App\DTO\AuthChangePasswordRequest;
use App\DTO\AuthForgotPasswordRequest;
use App\DTO\AuthLoginRequest;
use App\DTO\AuthRefreshRequest;
use App\DTO\AuthResetPasswordRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\AuditLogService;
use App\Service\PasswordResetService;
use App\Service\RefreshTokenService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class AuthController extends ApiController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly PasswordResetService $passwordResetService,
        private readonly UserRepository $userRepository,
        private readonly AuditLogService $auditLogService,
        #[Autowire(service: 'limiter.login')] private readonly RateLimiterFactory $loginLimiter,
        #[Autowire(service: 'limiter.otp')] private readonly RateLimiterFactory $otpLimiter
    ) {
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AuthLoginRequest();
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $limit = $this->loginLimiter->create(($request->getClientIp() ?? 'anon') . $dto->email)->consume(1);
        if (!$limit->isAccepted()) {
            return $this->json(['message' => 'Demasiados intentos. Intenta más tarde.'], 429);
        }

        try {
            $result = $this->authService->login($dto->email, $dto->password);
        } catch (\RuntimeException $e) {
            return $this->json(['message' => $e->getMessage()], 401);
        }

        $this->auditLogService->log('login', 'auth');

        return $this->json([
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'user' => $this->mapUser($result['user']),
        ]);
    }

    #[Route('/refresh', methods: ['POST'])]
    public function refresh(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AuthRefreshRequest();
        $dto->refresh_token = $data['refresh_token'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        try {
            $result = $this->authService->refresh($dto->refresh_token);
        } catch (\RuntimeException $e) {
            return $this->json(['message' => $e->getMessage()], 401);
        }

        return $this->json([
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'user' => $this->mapUser($result['user']),
        ]);
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AuthRefreshRequest();
        $dto->refresh_token = $data['refresh_token'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $this->refreshTokenService->revoke($dto->refresh_token);
        $this->auditLogService->log('logout', 'auth');

        return $this->json(['message' => 'Sesión finalizada']);
    }

    #[Route('/change-password', methods: ['POST'])]
    public function changePassword(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AuthChangePasswordRequest();
        $dto->current_password = $data['current_password'] ?? '';
        $dto->new_password = $data['new_password'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        try {
            $this->authService->changePassword($user, $dto->current_password, $dto->new_password);
        } catch (\RuntimeException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        $this->auditLogService->log('change_password', 'auth');

        return $this->json(['message' => 'Contraseña actualizada']);
    }

    #[Route('/forgot-password', methods: ['POST'])]
    public function forgotPassword(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AuthForgotPasswordRequest();
        $dto->email = $data['email'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $limit = $this->otpLimiter->create(($request->getClientIp() ?? 'anon') . $dto->email)->consume(1);
        if (!$limit->isAccepted()) {
            return $this->json(['message' => 'Demasiados intentos. Intenta más tarde.'], 429);
        }

        $user = $this->userRepository->findActiveByEmail($dto->email);
        if ($user) {
            $this->passwordResetService->requestReset($user);
        }

        return $this->json(['message' => 'Si el correo existe, enviaremos un OTP.']);
    }

    #[Route('/reset-password', methods: ['POST'])]
    public function resetPassword(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new AuthResetPasswordRequest();
        $dto->email = $data['email'] ?? '';
        $dto->otp = $data['otp'] ?? '';
        $dto->new_password = $data['new_password'] ?? '';

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $user = $this->userRepository->findActiveByEmail($dto->email);
        if (!$user) {
            return $this->json(['message' => 'OTP inválido o expirado.'], 400);
        }

        try {
            $this->passwordResetService->resetPassword($user, $dto->otp, $dto->new_password);
        } catch (\RuntimeException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        $this->auditLogService->log('reset_password', 'auth');

        return $this->json(['message' => 'Contraseña actualizada']);
    }

    private function mapUser($user): array
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
}

