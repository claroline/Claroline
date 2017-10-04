<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Performs a fresh installation of the platform.
 */
class PlatformInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:install')
            ->setDescription('Installs the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
         * Set the app/config directory in the installation state.
         * - No bundles.bup.ini
         * - Empty previous-installed.json
         */
        $kernel = $this->getContainer()->get('kernel');
        $rootDir = $kernel->getRootDir();
        $previous = $rootDir.'/config/previous-installed.json';
        @unlink($previous);
        file_put_contents($previous, '[]');

        $this
            ->getApplication()
            ->find('claroline:update')
            ->run(new ArrayInput([]), $output);
    }
}
