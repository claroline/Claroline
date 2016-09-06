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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
            [
                new InputArgument('directory_path', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
                new InputArgument('owner_username', InputArgument::REQUIRED, 'The owner username'),
            ]
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = [
            'directory_path' => 'Absolute path to the archive directory file: ',
            'owner_username' => 'The workspace owner username: ',
        ];

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
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($consoleLogger);
        $dirPath = $input->getArgument('directory_path');
        $username = $input->getArgument('owner_username');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $total = 0;
        $iterator = new \DirectoryIterator($dirPath);

        foreach ($iterator as $pathinfo) {
            if ($pathinfo->isFile()) {
                ++$total;
            }
        }

        $i = 0;
        $output->writeln('<comment> Removing workspaces... </comment>');

        foreach ($iterator as $pathinfo) {
            if ($pathinfo->isFile()) {
                $code = pathinfo($pathinfo->getFileName(), PATHINFO_FILENAME);
                $workspace = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);
                if ($workspace) {
                    $output->writeln("<error> Removing {$workspace->getCode()} </error>");
                    $this->getContainer()->get('claroline.manager.workspace_manager')->deleteWorkspace($workspace);
                    $om->clear();
                }
            }
        }

        foreach ($iterator as $pathinfo) {
            if ($pathinfo->isFile()) {
                ++$i;
                $output->writeln('<comment> Clearing object manager... </comment>');
                $om->clear();
                $user = $this->getContainer()->get('claroline.manager.user_manager')->getUserByUsername($username);
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->getContainer()->get('security.context')->setToken($token);
                $workspace = new Workspace();
                $workspace->setCreator($user);
                $newpath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.uniqid();
                $file = new File($newpath);
                $workspaceManager->create($workspace, $file);
                $output->writeln("<comment> Workspace {$i}/{$total} created. </comment>");
            }
        }
    }
}
