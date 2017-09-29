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

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ExportWorkspaceModelCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:export_model')
            ->setDescription('export workspace into archives');
        $this->setDefinition(
            [
                new InputArgument('export_directory', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
                new InputArgument('owner_username', InputArgument::REQUIRED, 'The user doing the action (because otherwise qti exo crashes)'),
                new InputArgument('code', InputArgument::OPTIONAL, 'The workspace code'),
            ]
        );
        $this->addOption(
            'personal',
            'p',
            InputOption::VALUE_NONE,
            'When set to true, export all personal workspaces'
        );
        $this->addOption(
            'standard',
            'r',
            InputOption::VALUE_NONE,
            'When set to true, export all standard workspaces'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $path = $input->getArgument('export_directory');
        $username = $input->getArgument('owner_username');
        //set the token for qti
        $user = $this->getContainer()->get('claroline.manager.user_manager')->getUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()->get('security.context')->setToken($token);

        $workspaces = [];
        $workspaceRepo = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\Workspace');

        $transferManager = $this->getContainer()->get('claroline.manager.transfer_manager');
        $verbosityLevelMap = [
                LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
            ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $transferManager->setLogger($consoleLogger);

        if ($code) {
            $workspaces[] = $this->getContainer()->get('claroline.manager.workspace_manager')->getWorkspaceByCode($code);
        }

        if ($input->getOption('personal')) {
            $workspaces = array_merge($workspaces, $workspaceRepo->findBy(['personal' => true]));
        }

        if ($input->getOption('standard')) {
            $workspaces = array_merge($workspaces, $workspaceRepo->findBy(['personal' => false]));
        }

        $i = 0;
        $count = count($workspaces);

        foreach ($workspaces as $workspace) {
            ++$i;
            $expPath = $path.'/'.$workspace->getCode().'.zip';
            $arch = $transferManager->export($workspace);
            $output->writeln("<comment>Moving to export directory ($i/$count)</comment>");
            rename($arch, $expPath);
        }
    }
}
