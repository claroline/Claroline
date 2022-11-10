<?php

namespace Claroline\CoreBundle\Library\GeoIp\Database;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MaxMindGeoIpDatabaseDownloader
{
    private const DOWNLOAD_URL = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&suffix=tar.gz';
    private const DATABASE_FILENAME = 'GeoLite2-City.mmdb';

    private $licenseKey;
    private $logger;
    private $httpClient;
    private $filesystem;
    private $tempDir;

    public function __construct(
        PlatformConfigurationHandler $config,
        string $tempDir,
        ?LoggerInterface $logger = null,
        ?HttpClientInterface $httpClient = null,
        ?Filesystem $filesystem = null
    ) {
        $this->licenseKey = $config->getParameter('geoip.maxmind_license_key');
        $this->logger = $logger ?? new NullLogger();
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->tempDir = $tempDir;
    }

    public function downloadDatabase(string $destinationDir, bool $catchExceptions = true): void
    {
        $response = $this->httpClient->request('GET', self::DOWNLOAD_URL, ['query' => ['license_key' => $this->licenseKey]]);
        $statusCode = $response->getStatusCode();

        if (200 !== $statusCode) {
            $this->logger->warning('GeoIp database file could not be downloaded.', ['status_code' => $statusCode, 'error' => $response->getInfo()['error'] ?? null]);

            return;
        }

        // Download and dump the archive as a temp file (chunk-by-chunk)
        $tempDir = "$this->tempDir/claroline-geoip";
        $this->filesystem->mkdir($tempDir);
        $tmpArchive = $this->filesystem->tempnam($tempDir, 'maxmind-geoip').'.tar.gz';

        foreach ($this->httpClient->stream($response) as $chunk) {
            try {
                $this->filesystem->appendToFile($tmpArchive, $chunk->getContent());
            } catch (ExceptionInterface | IOException $e) {
                if (!$catchExceptions) {
                    throw $e;
                }

                $this->logger->warning('GeoIp database could not be downloaded.', ['exception' => $e]);
                break;
            }
        }

        // Extract the archive to the temp dir
        $extractDirectory = $tempDir.'/extract';

        try {
            $archive = (new \PharData($tmpArchive));
            $archive->extractTo($extractDirectory, null, true);
        } catch (\RuntimeException | \PharException $e) {
            if (!$catchExceptions) {
                throw $e;
            }
            $this->logger->warning('GeoIp database archive could not be extracted to "%s".', ['exception' => $e]);

            return;
        }

        // Move the database file to the destination (overwrite if exists)
        $databaseDirectory = basename($archive->current()->getPathname());
        $this->filesystem->rename("$extractDirectory/$databaseDirectory/".self::DATABASE_FILENAME, $destinationDir.'/'.self::DATABASE_FILENAME, true);

        $this->logger->info('GeoIp database successfully downloaded.');

        // Cleanup filesystem
        $this->filesystem->remove($tempDir);
    }
}
