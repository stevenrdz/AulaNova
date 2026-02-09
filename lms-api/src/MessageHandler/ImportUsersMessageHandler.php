<?php

namespace App\MessageHandler;

use App\Entity\ImportBatch;
use App\Entity\ImportFile;
use App\Entity\ImportRowError;
use App\Entity\FileObject;
use App\Entity\Role;
use App\Entity\User;
use App\Message\ImportUsersMessage;
use App\Repository\UserRepository;
use App\Service\FileStorageService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
class ImportUsersMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly FileStorageService $fileStorageService
    ) {
    }

    public function __invoke(ImportUsersMessage $message): void
    {
        $batch = $this->entityManager->getRepository(ImportBatch::class)->find($message->batchId);
        if (!$batch) {
            return;
        }

        $batch->setStatus('processing');
        $this->entityManager->flush();

        $importFile = $this->entityManager->getRepository(ImportFile::class)
            ->findOneBy(['batch' => $batch]);

        if (!$importFile) {
            $batch->setStatus('failed');
            $this->entityManager->flush();
            return;
        }

        $file = $importFile->getFile();
        $tempPath = null;

        try {
            $contents = $this->fileStorageService->getObjectContents($file->getObjectKey(), $file->getBucket());
            $tempPath = tempnam(sys_get_temp_dir(), 'import_');
            if ($tempPath === false) {
                throw new \RuntimeException('No se pudo crear archivo temporal.');
            }
            file_put_contents($tempPath, $contents);

            $reader = IOFactory::createReaderForFile($tempPath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($tempPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (!$rows || count($rows) < 2) {
                throw new \RuntimeException('El archivo no contiene datos.');
            }

            $headerRow = array_shift($rows);
            $headerMap = $this->normalizeHeader($headerRow);

            $totalRows = 0;
            $success = 0;
            $errors = 0;
            $resultRows = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $payload = $this->mapRow($row, $headerMap);

                if ($this->isRowEmpty($payload)) {
                    continue;
                }

                $totalRows++;

                $email = $payload['email'] ?? '';
                $firstName = $payload['first_name'] ?? '';
                $lastName = $payload['last_name'] ?? '';
                $role = strtoupper($payload['role'] ?? 'ROLE_STUDENT');
                $password = $payload['password'] ?? null;

                $errorMessage = $this->validateRow($email, $firstName, $lastName, $role);
                if ($errorMessage !== null) {
                    $this->recordError($batch, $rowNumber, $errorMessage, $payload);
                    $resultRows[] = $this->resultRow($rowNumber, 'error', $errorMessage, $email, $firstName, $lastName, $role, null);
                    $errors++;
                    continue;
                }

                if ($this->userRepository->findActiveByEmail($email)) {
                    $errorMessage = 'El email ya existe.';
                    $this->recordError($batch, $rowNumber, $errorMessage, $payload);
                    $resultRows[] = $this->resultRow($rowNumber, 'error', $errorMessage, $email, $firstName, $lastName, $role, null);
                    $errors++;
                    continue;
                }

                if ($password === null || $password === '') {
                    $password = $this->generatePassword();
                }

                $user = new User();
                $user->setEmail($email)
                    ->setFirstName($firstName)
                    ->setLastName($lastName)
                    ->setPassword($this->passwordHasher->hashPassword($user, $password));

                $roleEntity = $this->getOrCreateRole($role);
                $user->addRoleEntity($roleEntity);

                $this->entityManager->persist($user);

                $resultRows[] = $this->resultRow($rowNumber, 'success', 'Creado', $email, $firstName, $lastName, $role, $password);
                $success++;
            }

            $batch->setTotalRows($totalRows)
                ->setSuccessCount($success)
                ->setErrorCount($errors)
                ->setStatus('completed');

            $resultFile = $this->createResultFile($batch, $resultRows);
            if ($resultFile) {
                $batch->setResultFile($resultFile);
                $this->entityManager->persist($resultFile);
            }

            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $batch->setStatus('failed');
            $this->entityManager->flush();
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    private function normalizeHeader(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $value) {
            if ($value === null) {
                continue;
            }
            $key = strtolower(trim((string) $value));
            $key = str_replace([' ', '-'], '_', $key);
            $map[$col] = $key;
        }

        return $map;
    }

    private function mapRow(array $row, array $headerMap): array
    {
        $data = [];
        foreach ($headerMap as $col => $key) {
            $data[$key] = isset($row[$col]) ? trim((string) $row[$col]) : null;
        }

        return $data;
    }

    private function isRowEmpty(array $payload): bool
    {
        $values = array_filter($payload, fn ($value) => $value !== null && $value !== '');
        return count($values) === 0;
    }

    private function validateRow(string $email, string $firstName, string $lastName, string $role): ?string
    {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Email inv?lido.';
        }
        if ($firstName === '') {
            return 'first_name es requerido.';
        }
        if ($lastName === '') {
            return 'last_name es requerido.';
        }
        if (!in_array($role, ['ROLE_STUDENT', 'ROLE_TEACHER', 'ROLE_ADMIN'], true)) {
            return 'Rol inv?lido.';
        }

        return null;
    }

    private function recordError(ImportBatch $batch, int $rowNumber, string $message, array $payload): void
    {
        $error = new ImportRowError($batch, $rowNumber, $message);
        $error->setRawData($payload);
        $this->entityManager->persist($error);
    }

    private function generatePassword(): string
    {
        return 'LMS' . bin2hex(random_bytes(4));
    }

    private function getOrCreateRole(string $roleName): Role
    {
        $repo = $this->entityManager->getRepository(Role::class);
        $role = $repo->findOneBy(['name' => $roleName]);

        if (!$role) {
            $role = new Role($roleName);
            $this->entityManager->persist($role);
            $this->entityManager->flush();
        }

        return $role;
    }

    private function resultRow(int $rowNumber, string $status, string $message, string $email, string $firstName, string $lastName, string $role, ?string $password): array
    {
        return [
            'row' => $rowNumber,
            'status' => $status,
            'message' => $message,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'password' => $password ?? '',
        ];
    }

    private function createResultFile(ImportBatch $batch, array $rows): ?FileObject
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return null;
        }

        fputcsv($handle, ['row', 'status', 'message', 'email', 'first_name', 'last_name', 'role', 'password']);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        $key = 'imports/results/' . date('Y/m') . '/batch_' . $batch->getId() . '.csv';
        $this->fileStorageService->putObject($key, $csv, 'text/csv');

        $file = new FileObject($key, $this->fileStorageService->getBucket(), 'import_users_batch_' . $batch->getId() . '.csv', 'text/csv', strlen($csv));
        $file->setCreatedBy($batch->getCreatedBy());

        return $file;
    }
}
