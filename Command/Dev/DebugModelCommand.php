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
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class DebugModelCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:model:debug')
            ->setDescription('Create a workspace from a model');
        $this->setDefinition(
            array(
                new InputArgument('owner', InputArgument::REQUIRED, 'The workspace owner'),
                new InputArgument('model', InputArgument::REQUIRED, 'The workspace model'),
                new InputArgument('code', InputArgument::REQUIRED, 'The workspace code'),
                new InputArgument('name', InputArgument::REQUIRED, 'The workspace name')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'owner' => 'The workspace owner: ',
            'model' => 'The workspace model: ',
            'code' => 'The workspace code: ',
            'name' => 'The workspace name: '
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
        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
        );

        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $root = $om->getRepository('Claroline\CoreBundle\Entity\User')->findOneByUsername($input->getArgument('owner'));
        $model = $om->getRepository('Claroline\CoreBundle\Entity\Model\WorkspaceModel')->findOneByName($input->getArgument('model'));
        $workspaceManager->createWorkspaceFromModel($model, $root, $input->getArgument('name'), $input->getArgument('code'));
    }
}
