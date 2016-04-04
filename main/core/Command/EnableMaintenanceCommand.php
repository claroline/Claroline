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
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;

class EnableMaintenanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:maintenance:enable')
            ->setDescription('Enable maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        MaintenanceHandler::enableMaintenance();
    }
}
