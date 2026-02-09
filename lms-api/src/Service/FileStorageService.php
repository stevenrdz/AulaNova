<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FileStorageService
{
    private string $bucket;

    public function __construct(
        private readonly S3Client $s3Client,
        #[Autowire('%app.minio.bucket%')] string $bucket
    ) {
        $this->bucket = $bucket;
    }

    public function createPresignedUpload(string $filename, string $mimeType): array
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $random = bin2hex(random_bytes(16));
        $key = 'uploads/' . date('Y/m') . '/' . $random . ($extension ? '.' . $extension : '');

        $command = $this->s3Client->getCommand('PutObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
            'ContentType' => $mimeType,
        ]);

        $request = $this->s3Client->createPresignedRequest($command, '+15 minutes');

        return [
            'url' => (string) $request->getUri(),
            'key' => $key,
            'bucket' => $this->bucket,
            'expires_in' => 900,
        ];
    }

    public function createPresignedDownload(
        string $key,
        string $mimeType,
        string $filename,
        ?string $bucket = null,
        string $disposition = 'attachment'
    ): array {
        $bucket = $bucket ?: $this->bucket;
        $disposition = strtolower($disposition) === 'inline' ? 'inline' : 'attachment';
        $safeName = trim($filename) !== '' ? $filename : 'download';

        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $key,
            'ResponseContentType' => $mimeType ?: 'application/octet-stream',
            'ResponseContentDisposition' => sprintf('%s; filename="%s"', $disposition, addcslashes($safeName, '"\\')),
        ]);

        $request = $this->s3Client->createPresignedRequest($command, '+15 minutes');

        return [
            'url' => (string) $request->getUri(),
            'key' => $key,
            'bucket' => $bucket,
            'expires_in' => 900,
        ];
    }

    public function getObjectContents(string $key, ?string $bucket = null): string
    {
        $bucket = $bucket ?: $this->bucket;
        $result = $this->s3Client->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);

        return (string) $result['Body'];
    }

    public function getObjectStream(string $key, ?string $bucket = null)
    {
        $bucket = $bucket ?: $this->bucket;
        $result = $this->s3Client->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);

        $body = $result['Body'];
        if ($body instanceof \Psr\Http\Message\StreamInterface) {
            return $body->detach();
        }

        return null;
    }

    public function putObject(string $key, string $body, string $mimeType = 'text/plain', ?string $bucket = null): void
    {
        $bucket = $bucket ?: $this->bucket;
        $this->s3Client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'Body' => $body,
            'ContentType' => $mimeType,
        ]);
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }
}
