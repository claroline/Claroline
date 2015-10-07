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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;

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
                new InputArgument('code', InputArgument::REQUIRED, 'The workspace code')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = array(
            'code' => 'The workspace code: '
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
        $consoleLogger = ConsoleLogger::get($output);
        $code = $input->getArgument('code');

        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
        $resourceManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $rightsManager = $this->getContainer()->get('claroline.manager.rights_manager');
        $rightsManager->setLogger($consoleLogger);

        $workspace = $workspaceManager->getWorkspaceByCode($code);
        $roles = $roleManager->getWorkspaceConfigurableRoles($workspace);
        $root = $resourceManager->getWorkspaceRoot($workspace);

        foreach ($roles as $role) {
            $rightsManager->editPerms(5, $role, $root, true);
        }
    }
}
