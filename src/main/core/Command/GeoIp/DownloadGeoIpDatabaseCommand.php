<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\GeoIp;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\GeoIp\Database\MaxMindGeoIpDatabaseDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadGeoIpDatabaseCommand extends Command
{
    private $maxmindLicenseKey;
    private $downloader;
    private $destinationDir;

    public function __construct(PlatformConfigurationHandler $config, MaxMindGeoIpDatabaseDownloader $downloader, string $destinationDir)
    {
        $this->maxmindLicenseKey = $config->getParameter('geoip.maxmind_license_key');
        $this->downloader = $downloader;
        $this->destinationDir = is_dir($destinationDir) ? $destinationDir : dirname($destinationDir);

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->maxmindLicenseKey) {
            $io->comment('The "geoip.maxmind_license_key" platform option is not set, skipping.');

            return 0; // always exit with success, this is ran as an optional composer post-innstall script
        }

        $this->downloader->downloadDatabase($this->destinationDir);

        $io->comment('Geoip database successfully downloaded.');

        return 0;
    }
}
