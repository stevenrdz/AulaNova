<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FileStorageService
{
    private string $bucket;

    public function __construct(
        private readonly S3Client $s3Client,
        #[Autowire('%app.minio.bucket%')] string $bucket,
        #[Autowire('%app.minio.public_endpoint%')] private readonly ?string $publicEndpoint = null,
        #[Autowire('%app.minio.access_key%')] private readonly ?string $accessKey = null,
        #[Autowire('%app.minio.secret_key%')] private readonly ?string $secretKey = null,
        #[Autowire('%app.minio.region%')] private readonly ?string $region = null,
        #[Autowire('%app.minio.use_path_style%')] private readonly bool $usePathStyle = true
    ) {
        $this->bucket = $bucket;
    }

    private ?S3Client $publicClient = null;

    public function createPresignedUpload(string $filename, string $mimeType): array
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $random = bin2hex(random_bytes(16));
        $key = 'uploads/' . date('Y/m') . '/' . $random . ($extension ? '.' . $extension : '');

        $client = $this->getPresignClient();
        $command = $client->getCommand('PutObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
            'ContentType' => $mimeType,
        ]);

        $request = $client->createPresignedRequest($command, '+15 minutes');

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

        $client = $this->getPresignClient();
        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $key,
            'ResponseContentType' => $mimeType ?: 'application/octet-stream',
            'ResponseContentDisposition' => sprintf('%s; filename="%s"', $disposition, addcslashes($safeName, '"\\')),
        ]);

        $request = $client->createPresignedRequest($command, '+15 minutes');

        return [
            'url' => (string) $request->getUri(),
            'key' => $key,
            'bucket' => $bucket,
            'expires_in' => 900,
        ];
    }

    private function getPresignClient(): S3Client
    {
        if (!$this->publicEndpoint) {
            return $this->s3Client;
        }

        if ($this->publicClient instanceof S3Client) {
            return $this->publicClient;
        }

        $region = $this->region ?: 'us-east-1';
        $credentials = null;
        if ($this->accessKey && $this->secretKey) {
            $credentials = [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ];
        }

        $config = [
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $this->publicEndpoint,
            'use_path_style_endpoint' => $this->usePathStyle,
        ];
        if ($credentials) {
            $config['credentials'] = $credentials;
        }
        $this->publicClient = new S3Client($config);

        return $this->publicClient;
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
