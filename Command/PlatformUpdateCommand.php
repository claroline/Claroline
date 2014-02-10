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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Updates, installs or uninstalls the core and plugin bundles, following
 * the operation order logged in *app/config/operations.xml* during
 * composer execution.
 */
class PlatformUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:update')
            ->setDescription('Updates, installs or uninstalls the claroline packages brought by composer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Updating the platform...');
        $executor = $this->getContainer()->get('claroline.installation.operation_executor');
        $executor->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $executor->execute();
    }
}
