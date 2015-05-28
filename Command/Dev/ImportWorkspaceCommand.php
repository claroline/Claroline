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
use Claroline\CoreBundle\Library\Workspace\Configuration;

class ImportWorkspaceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:import:workspace')
            ->setDescription('import a workspace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $archive = $this->getContainer()->getParameter('claroline.param.templates_directory') . 'default.zip';
        $config = Configuration::fromTemplate($archive);
        $this->getContainer()->get('claroline.manager.transfert_manager')->import($config);
    }
} 
