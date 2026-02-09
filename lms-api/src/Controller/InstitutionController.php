<?php

namespace App\Controller;

use App\DTO\InstitutionSettingsRequest;
use App\Entity\InstitutionSettings;
use App\Service\AuditLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/institution')]
#[IsGranted('ROLE_ADMIN')]
class InstitutionController extends ApiController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuditLogService $auditLogService
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $settings = $this->getSettings();

        return $this->json([
            'data' => [
                'logo_url' => $settings->getLogoUrl(),
                'primary_color' => $settings->getPrimaryColor(),
            ],
        ]);
    }

    #[Route('', methods: ['PUT'])]
    public function update(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = new InstitutionSettingsRequest();
        $dto->logo_url = $data['logo_url'] ?? null;
        $dto->primary_color = $data['primary_color'] ?? null;

        if ($errorResponse = $this->validateDto($dto, $validator)) {
            return $errorResponse;
        }

        $settings = $this->getSettings();
        if ($dto->logo_url !== null) {
            $settings->setLogoUrl($dto->logo_url);
        }
        if ($dto->primary_color !== null) {
            $settings->setPrimaryColor($dto->primary_color);
        }

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $this->auditLogService->log('update', 'institution_settings', (string) $settings->getId());

        return $this->json([
            'data' => [
                'logo_url' => $settings->getLogoUrl(),
                'primary_color' => $settings->getPrimaryColor(),
            ],
        ]);
    }

    private function getSettings(): InstitutionSettings
    {
        $repo = $this->entityManager->getRepository(InstitutionSettings::class);
        $settings = $repo->findOneBy([]);

        if (!$settings) {
            $settings = new InstitutionSettings();
            $this->entityManager->persist($settings);
            $this->entityManager->flush();
        }

        return $settings;
    }
}
