<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IconSetBuilderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:icon_set:check')
            ->setDescription('This command allow you to restore the icon set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = ConsoleLogger::get($output);
        $this->getContainer()->get('claroline.manager.icon_set_manager')->setLogger($logger);
        $this->getContainer()->get('claroline.manager.icon_set_manager')->addDefaultIconSets();
    }
}
