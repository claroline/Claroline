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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkspaceToolIntegrityCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace_tool:check')
            ->setDescription('Checks the workspace tools integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $this->getContainer()->get('doctrine.orm.entity_manager')->createQuery(
          '
            SELECT w from Claroline\CoreBundle\Entity\Workspace\Workspace w
            LEFT JOIN w.orderedTools ot
            WHERE not exists (
              SELECT ot2
              FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot2
              JOIN ot2.workspace w2
              WHERE w2.id = w.id
            )
          '
        );

        $workspaces = $query->getResult();

        foreach ($workspaces as $workspace) {
            $output->writeln('Restoring tools for '.$workspace->getName().'...');
            $this->getContainer()->get('claroline.manager.tool_manager')->addMissingWorkspaceTools($workspace);
        }
    }
}
