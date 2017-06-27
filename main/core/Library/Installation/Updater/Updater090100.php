<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater090100 extends Updater
{
    private $container;
    private $dataWebDir;
    private $fileSystem;
    private $publicFilesDir;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->dataWebDir = $container->getParameter('claroline.param.data_web_dir');
        $this->fileSystem = $container->get('filesystem');
        $this->logger = $logger;
        $this->publicFilesDir = $container->getParameter('claroline.param.public_files_directory');
    }

    public function postUpdate()
    {
        $this->createPublicDirectory();
    }

    private function createPublicDirectory()
    {
        if (!$this->fileSystem->exists($this->publicFilesDir)) {
            $this->log('Creating public directory in files directory...');
            $this->fileSystem->mkdir($this->publicFilesDir, 0775);
            $this->fileSystem->chmod($this->publicFilesDir, 0775, 0000, true);
        }

        if (!$this->fileSystem->exists($this->dataWebDir)) {
            $this->log('Creating symlink to public directory of files directory in web directory...');
            $this->fileSystem->symlink($this->publicFilesDir, $this->dataWebDir);
        }
    }
}
