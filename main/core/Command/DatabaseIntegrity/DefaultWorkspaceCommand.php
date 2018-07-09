<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 17/10/17
 * Time: 14:10.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultWorkspaceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:build-default')
            ->setDescription('This command allow you to rebuild the default workspaces');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->getDefaultModel(false, true);
        $workspaceManager->getDefaultModel(true, true);
    }
}
