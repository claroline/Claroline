<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Archive;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveWorkspaceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:archive')
            ->setDescription('Archive workspace by request')
            ->setDefinition([
                new InputArgument('dql', InputArgument::REQUIRED, 'The dql request - ie: "SELECT w FROM Claroline\CoreBundle\Entity\Workspace\Workspace w WHERE w.name LIKE \'%fifi%\'"'
              ),
           ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dql = $input->getArgument('dql');

        $query = $this->getContainer()->get('doctrine.orm.entity_manager')->createQuery($dql);

        $workspaces = $query->getResult();
        $i = 0;
        $cw = count($workspaces);
        $output->writeln($cw.' found.');

        foreach ($workspaces as $workspace) {
            ++$i;
            $output->writeln('Updating workspace '.$workspace->getName().': '.$i.'/'.$cw);
            $this->getContainer()->get('claroline.manager.workspace_manager')->archive($workspace);

            if (0 === $i % 500) {
                $this->getContainer()->get('claroline.persistence.object_manager')->flush();
            }
        }

        $this->getContainer()->get('claroline.persistence.object_manager')->flush();
    }
}
