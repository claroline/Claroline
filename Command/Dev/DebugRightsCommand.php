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
class DebugRightsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:rights:debug')
            ->setDescription('Recursively change the permissions of a root directory.');
        $this->setDefinition(
            array(
                new InputArgument('code', InputArgument::REQUIRED, 'The workspace code'),
                new InputArgument('role', InputArgument::REQUIRED, 'The new role name')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = array(
            'code' => 'The workspace code: ',
            'role' => 'The new role name: '
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
        $code = $input->getArgument('code');
        $role = $input->getArgument('role');

        $workspace = $this->getContainer()->get('claroline.manager.workspace_manager')->getWorkspaceByCode($code);
        $root = $this->getContainer()->get('claroline.manager.resource_manager')->getWorkspaceRoot($workspace);
        $roleEntity = $this->getContainer()->get('claroline.manager.role_manager')->createBaseRole('ROLE_' . $role, $role);
        $rightsManager = $this->getContainer()->get('claroline.manager.rights_manager');
        $rightsManager->setLogger($consoleLogger);
        $rightsManager->editPerms(1, $roleEntity, $root, true);
    }
}
