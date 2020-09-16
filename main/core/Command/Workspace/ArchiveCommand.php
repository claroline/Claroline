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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveCommand extends Command
{
    private $em;
    private $om;
    private $workspaceManager;

    public function __construct(EntityManagerInterface $em, ObjectManager $om, WorkspaceManager $workspaceManager)
    {
        $this->em = $em;
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Archive workspace by request')
            ->setDefinition([
                new InputArgument('dql', InputArgument::REQUIRED, 'The dql request - ie: "SELECT w FROM Claroline\CoreBundle\Entity\Workspace\Workspace w WHERE w.name LIKE \'%fifi%\'"'
              ),
           ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dql = $input->getArgument('dql');

        $query = $this->em->createQuery($dql);

        $workspaces = $query->getResult();
        $i = 0;
        $cw = count($workspaces);
        $output->writeln($cw.' found.');

        foreach ($workspaces as $workspace) {
            ++$i;
            $output->writeln('Updating workspace '.$workspace->getName().': '.$i.'/'.$cw);
            $this->workspaceManager->archive($workspace);

            if (0 === $i % 500) {
                $this->om->flush();
            }
        }

        $this->om->flush();

        return 0;
    }
}
