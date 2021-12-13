<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    private $em;
    private $workspaceManager;

    public function __construct(EntityManagerInterface $em, WorkspaceManager $workspaceManager)
    {
        $this->em = $em;
        $this->workspaceManager = $workspaceManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('export workspace archive');
        $this->setDefinition([
            new InputArgument('code', InputArgument::OPTIONAL, 'The workspace code'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workspace = $this->em->getRepository(Workspace::class)->findOneByCode($input->getArgument('code'));
        $path = $this->workspaceManager->export($workspace);

        $output->writeln($path);

        return 0;
    }
}
