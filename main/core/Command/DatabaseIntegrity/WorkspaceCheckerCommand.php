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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkspaceCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:check')
            ->setDescription('Checks the workspace tools integrity of the platform.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'All tools and workspace')
            ->addOption('flag', 'f', InputOption::VALUE_NONE, 'Set the personal workspace flag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Workspace tool restoration...');

        if ($input->getOption('flag')) {
            $consoleLogger = ConsoleLogger::get($output);
            $this->getContainer()->get('claroline.manager.workspace_manager')->setLogger($consoleLogger);
            $this->getContainer()->get('claroline.manager.workspace_manager')->setWorkspacesFlag();
        }

        if ($input->getOption('all')) {
            $workspaces = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Workspace\Workspace')
              ->findBy(['personal' => false]);
        } else {
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
        }

        foreach ($workspaces as $workspace) {
            $output->writeln('Restoring tools for '.$workspace->getName().'...');
            $this->getContainer()->get('claroline.manager.tool_manager')->addMissingWorkspaceTools($workspace);
        }

        $output->writeln('Workspace organization restoration...');

        $query = $this->getContainer()->get('doctrine.orm.entity_manager')->createQuery(
          '
            SELECT w from Claroline\CoreBundle\Entity\Workspace\Workspace w
            LEFT JOIN w.organizations o
            WHERE o IS null
          '
        );

        $workspaces = $query->getResult();

        $defaultOrganization = $this->getContainer()->get('claroline.manager.organization.organization_manager')->getDefault();
        $om = $this->getContainer()->get('claroline.persistence.object_manager');

        foreach ($workspaces as $workspace) {
            $output->writeln('Restoring organization for '.$workspace->getName().'...');
            $workspace->addOrganization($defaultOrganization);
            $om->persist($workspace);
        }

        $om->flush();
    }
}
