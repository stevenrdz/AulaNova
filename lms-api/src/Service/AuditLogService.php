<?php

namespace App\Service;

use App\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly RequestStack $requestStack
    ) {
    }

    public function log(string $action, string $entityType, ?string $entityId = null, ?array $metadata = null): void
    {
        $log = new AuditLog($action, $entityType, $entityId);
        $user = $this->security->getUser();
        if ($user instanceof \App\Entity\User) {
            $log->setUser($user);
        }
        $log->setMetadata($metadata);

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
            $log->setUserAgent($request->headers->get('User-Agent'));
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
