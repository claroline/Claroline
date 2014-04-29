<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportWorkspaceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:import:workspace')
            ->setDescription('import a workspace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = __DIR__ . "/ws.zip";
        $this->getContainer()->get('claroline.manager.transfert_manager')->initialize($path);
        $this->getContainer()->get('claroline.manager.transfert_manager')->import();
    }
} 