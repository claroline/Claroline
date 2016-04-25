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
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class ImportWorkspaceModelCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:import_model')
            ->setDescription('Create a workspace from a zip archive (for debug purpose)');
        $this->setDefinition(
            array(
                new InputArgument('archive_path', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
                new InputArgument('owner_username', InputArgument::REQUIRED, 'The owner username'),
                new InputArgument('code', InputArgument::REQUIRED, 'The workspace code'),
                new InputArgument('name', InputArgument::REQUIRED, 'The workspace name'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = array(
            'archive_path' => 'Absolute path to the zip file: ',
            'owner_username' => 'The workspace owner username: ',
            'code' => 'The workspace code: ',
            'name' => 'The workspace name: ',
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
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        );
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($consoleLogger);
        $validator = $this->getContainer()->get('validator');
        $template = $input->getArgument('archive_path');
        $username = $input->getArgument('owner_username');
        $code = $input->getArgument('code');
        $name = $input->getArgument('name');
        $user = $this->getContainer()->get('claroline.manager.user_manager')->getUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()->get('security.context')->setToken($token);
        $config = Configuration::fromTemplate($template);
        $config->setWorkspaceName($name);
        $config->setWorkspaceCode($code);
        $config->setDisplayable(true);
        $config->setSelfRegistration(true);
        $config->setRegistrationValidation(true);
        $config->setSelfUnregistration(true);
        $config->setWorkspaceDescription(true);
        $workspaceManager->create($config, $user);
        $workspaceManager->importRichText();
    }
}
