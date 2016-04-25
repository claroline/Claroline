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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportWorkspaceModelCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:export_model')
            ->setDescription('export a workspace');
        $this->setDefinition(
            array(
                new InputArgument('archive_path', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
                new InputArgument('code', InputArgument::REQUIRED, 'The owner username'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = array(
            'archive_path' => 'Absolute path to the zip file: ',
            'code' => 'The workspace code: ',
        );

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            $argumentName,
            function ($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('archive_path');
        $code = $input->getArgument('code');
        $workspace = $this->getContainer()->get('claroline.manager.workspace_manager')->getWorkspaceByCode($code);
        $arch = $this->getContainer()->get('claroline.manager.transfert_manager')->export($workspace);
        rename($arch, $path);
    }
}
