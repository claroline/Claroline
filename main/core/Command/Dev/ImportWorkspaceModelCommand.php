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
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addOption(
            'skip',
            null,
            InputOption::VALUE_NONE,
            'When set to true, skip existing workspaces'
        );
        $this->addOption(
            'uncompressed',
            'u',
            InputOption::VALUE_NONE,
            'When set to true, will try to import directory_path parameter as an uncompressed template'
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
        $skip = $input->getOption('skip');
        $uncompressed = $input->getOption('uncompressed');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $total = 0;

        //import directory content
        if (is_dir($dirPath) && !$uncompressed) {
            $iterator = new \DirectoryIterator($dirPath);
            //delete existing workspaces
            if (!$skip) {
                $output->writeln('<comment> Removing workspaces... </comment>');
                foreach ($iterator as $pathinfo) {
                    if ($pathinfo->isFile()) {
                        $this->cleanWorkspace(new File($pathinfo->getPathname()), $output, $om);
                    }
                }
            }
            //count
            foreach ($iterator as $pathinfo) {
                if ($pathinfo->isFile() && $pathinfo->getExtension() === 'zip') {
                    ++$total;
                }
            }
            //import workspaces
            $i = 1;
            foreach ($iterator as $pathinfo) {
                if ($pathinfo->isFile()) {
                    $this->importWorkspace(new File($pathinfo->getPathname()), $username, $output, $workspaceManager, $om, $i, $total, $skip);
                    ++$i;
                }
            }
        //import one specific workspace
        } else {
            if ($uncompressed) {
                $this->importUncompressedWorkspace($dirPath, $username, $output, $workspaceManager, $om, 1, 1, $skip);
            } else {
                $file = new File($dirPath);
                if (!$skip) {
                    $output->writeln('<comment> Removing workspace... </comment>');
                    $this->cleanWorkspace($file, $output, $om);
                }
                $this->importWorkspace($file, $username, $output, $workspaceManager, $om, 1, 1, $skip);
            }
        }
    }

    protected function importWorkspace(File $file, $username, OutputInterface $output, WorkspaceManager $workspaceManager, ObjectManager $om, $i, $total, $skip = false)
    {
        $workspace = null;
        if ($skip) {
            $workspace = $this->getWorkspaceFromCode(pathinfo($file->getFileName(), PATHINFO_FILENAME), $output, $om);
        }

        if ($workspace === null) {
            $output->writeln('<comment> Clearing object manager... </comment>');
            $om->clear();
            $user = $this->getContainer()->get('claroline.manager.user_manager')->getUserByUsernameOrMail($username, $username);
            if ($user === null) {
                throw new \Exception('User not found : '.$username);
            }
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->getContainer()->get('security.context')->setToken($token);
            $workspace = new Workspace();
            $workspace->setCreator($user);
            $workspaceManager->create($workspace, $file);
            $output->writeln("<comment> Workspace {$i}/{$total} created. </comment>");
        } else {
            $output->writeln("<comment> Workspace {$workspace->getCode()} already exists. {$i}/{$total} skipped.</comment>");
        }
    }

    protected function importUncompressedWorkspace($dir, $username, OutputInterface $output, WorkspaceManager $workspaceManager, ObjectManager $om, $i, $total, $skip = false)
    {
        $workspace = null;
        if ($skip) {
            $workspace = $this->getWorkspaceFromCode(basename($dir), $output, $om);
        }

        if ($workspace === null) {
            $output->writeln('<comment> Clearing object manager... </comment>');
            $om->clear();
            $user = $this->getContainer()->get('claroline.manager.user_manager')->getUserByUsernameOrMail($username, $username);
            if ($user === null) {
                throw new \Exception('User not found : '.$username);
            }
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->getContainer()->get('security.context')->setToken($token);
            $workspace = new Workspace();
            $workspace->setCreator($user);
            $workspaceManager->createFromTemplate($workspace, $dir);
            $output->writeln("<comment> Workspace {$i}/{$total} created. </comment>");
        } else {
            $output->writeln("<comment> Workspace {$workspace->getCode()} already exists. {$i}/{$total} skipped.</comment>");
        }
    }

    protected function cleanWorkspace(File $file, OutputInterface $output, ObjectManager $om)
    {
        $workspace = $this->getWorkspaceFromCode(pathinfo($file->getFileName(), PATHINFO_FILENAME), $output, $om);
        if ($workspace) {
            $output->writeln("<comment> Removing {$workspace->getCode()} </comment>");
            $this->getContainer()->get('claroline.manager.workspace_manager')->deleteWorkspace($workspace);
            $om->clear();
        }
    }

    protected function getWorkspaceFromCode($code, OutputInterface $output, ObjectManager $om)
    {
        return $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);
    }
}
