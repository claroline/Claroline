<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Maintenance;

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableMaintenanceCommand extends Command
{
    protected function configure()
    {
        $this->setDescription('Enable maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        MaintenanceHandler::enableMaintenance();

        return 0;
    }
}
