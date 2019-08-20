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

use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportWorkspaceModelCommand extends ContainerAwareCommand implements AdminCliCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:archive-export')
            ->setDescription('export workspace archive');
        $this->setDefinition(
            [
                new InputArgument('code', InputArgument::OPTIONAL, 'The workspace code'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $workspace = $container->get('doctrine.orm.entity_manager')->getRepository(Workspace::class)->findOneByCode($input->getArgument('code'));
        $path = $container->get('claroline.manager.workspace.transfer')->export($workspace);
        $output->writeln($path);
    }
}
