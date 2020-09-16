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

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkspaceCheckerCommand extends Command
{
    private $om;
    private $workspaceManager;
    private $entityManager;
    private $toolManager;
    private $organizationManager;

    public function __construct(ObjectManager $om, WorkspaceManager $workspaceManager, EntityManagerInterface $entityManager, ToolManager $toolManager, OrganizationManager $organizationManager)
    {
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->entityManager = $entityManager;
        $this->toolManager = $toolManager;
        $this->organizationManager = $organizationManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks the workspace tools integrity of the platform.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'All tools and workspace')
            ->addOption('flag', 'f', InputOption::VALUE_NONE, 'Set the personal workspace flag');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Workspace tool restoration...');

        if ($input->getOption('flag')) {
            $consoleLogger = ConsoleLogger::get($output);
            $this->workspaceManager->setLogger($consoleLogger);
            $this->workspaceManager->setWorkspacesFlag();
        }

        if ($input->getOption('all')) {
            $workspaces = $this->entityManager->getRepository('ClarolineCoreBundle:Workspace\Workspace')
              ->findBy(['personal' => false]);
        } else {
            $query = $this->entityManager->createQuery(
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
            $this->toolManager->addMissingWorkspaceTools($workspace);
        }

        $output->writeln('Workspace organization restoration...');

        $query = $this->entityManager->createQuery(
          '
            SELECT w from Claroline\CoreBundle\Entity\Workspace\Workspace w
            LEFT JOIN w.organizations o
            WHERE o IS null
          '
        );

        $workspaces = $query->getResult();

        $defaultOrganization = $this->organizationManager->getDefault();

        foreach ($workspaces as $workspace) {
            $output->writeln('Restoring organization for '.$workspace->getName().'...');
            $workspace->addOrganization($defaultOrganization);
            $this->om->persist($workspace);
        }

        $this->om->flush();

        return 0;
    }
}
