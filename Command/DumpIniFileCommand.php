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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Recorder;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class DumpIniFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:ini_file:dump')
            ->setDescription('Dump the ini file.');
        $this->setDefinition(array());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vendorDir = $this->getContainer()->getParameter('claroline.param.vendor_directory');
        $configDir = $this->getContainer()->getParameter('kernel.root_dir') . '/config';
        $recorder = new Recorder(
            new Detector($vendorDir),
            new BundleHandler($configDir . '/bundles.ini'),
            new OperationHandler($configDir . '/operations.xml'),
            $vendorDir
        );

        $recorder->buildBundleFile();
    }
}
