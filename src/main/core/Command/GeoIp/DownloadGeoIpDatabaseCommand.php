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

use Claroline\CoreBundle\Library\GeoIp\Database\MaxMindGeoIpDatabaseDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadGeoIpDatabaseCommand extends Command
{
    private $downloader;
    private $destinationDir;

    public function __construct(MaxMindGeoIpDatabaseDownloader $downloader, string $destinationDir)
    {
        $this->downloader = $downloader;
        $this->destinationDir = is_dir($destinationDir) ? $destinationDir : dirname($destinationDir);

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->downloader->downloadDatabase($this->destinationDir);

        return 0;
    }
}
