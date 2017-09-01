<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Removal;

use Claroline\CoreBundle\Command\Traits\AskRolesTrait;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class RemoveWorkspaceCommand extends ContainerAwareCommand
{
    private $force = false;
    private $output = null;
    private $input = null;

    use AskRolesTrait;

    const BATCH_SIZE = 25;

    protected function configure()
    {
        $this->setName('claroline:remove:workspaces')
            ->setDescription('Remove workspaces');

        $this->addOption(
            'personal',
            'p',
            InputOption::VALUE_NONE,
            'When set to true, removes the personal workspaces'
        );

        $this->addOption(
            'standard',
            'i',
            InputOption::VALUE_NONE,
            'When set to true, removes the standard workspaces'
        );

        $this->addOption(
            'empty',
            'l',
            InputOption::VALUE_NONE,
            'When set to true, removes the empty one'
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'When set to true, doesn\'t ask for a confirmation'
        );
    }

    private function setForce($force)
    {
        $this->force = $force;
    }

    private function getForce()
    {
        return $this->force;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    public function getInput()
    {
        return $this->input;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $personal = $input->getOption('personal');
        $standard = $input->getOption('standard');
        $this->setForce($input->getOption('force'));
        $this->setInput($input);
        $this->setOutput($output);

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];

        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($consoleLogger);

        if ($personal) {
            $question = new ConfirmationQuestion('Remove all personal Workspaces ? y/n [y] ', true);
            $all = $helper->ask($input, $output, $question);
            $question = new ConfirmationQuestion('Include workspaces from removed users (orphans) ? y/n [y] ', true);
            $includeOrphans = $helper->ask($input, $output, $question);
            $rolesSearch = $this->askRoles($all, $input, $output, $this->getContainer(), $helper);
            $this->deletePersonalWorkspace($all, $rolesSearch, $includeOrphans);
        }

        if ($standard) {
            $question = new Question('Filter on code (continue if no filter)', null);
            $code = $helper->ask($input, $output, $question);
            $question = new Question('Filter on name (continue if no filter)', null);
            $name = $helper->ask($input, $output, $question);
            $this->deleteWorkspaceByCodeAndName($code, $name);
        }
    }

    private function deleteWorkspaceByCodeAndName($code, $name)
    {
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $toDelete = $workspaceManager->getNonPersonalByCodeAndName($code, $name);

        if (count($toDelete) > 0) {
            $this->confirmWorkspaceDelete($toDelete);
        }
    }

    /**
     * batch removal. Recursive so the UOW isn't too massive.
     */
    private function deletePersonalWorkspace($all, $rolesSearch, $includeOrphans)
    {
        $empty = $this->getInput()->getOption('empty');
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspacesToDelete = $all ?
            $workspaceManager->getPersonalWorkspaceExcludingRoles($rolesSearch, $includeOrphans, $empty, null, self::BATCH_SIZE) :
            $workspaceManager->getPersonalWorkspaceByRolesIncludingGroups($rolesSearch, $includeOrphans, $empty, null, self::BATCH_SIZE);

        if (count($workspacesToDelete) > 0) {
            $this->confirmWorkspaceDelete($workspacesToDelete);
            $this->deletePersonalWorkspace($all, $rolesSearch, $includeOrphans);
        }
    }

    private function confirmWorkspaceDelete(array $workspaces)
    {
        $helper = $this->getHelper('question');
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');

        foreach ($workspaces as $workspace) {
            $this->getOutput()->writeln("{$workspace->getId()}: {$workspace->getName()} - {$workspace->getCode()} ");
        }

        if (!$this->getForce()) {
            $count = count($workspaces);
            $question = new ConfirmationQuestion("Do you really want to remove theses {$count} workspaces ? y/n [y] ", true);
            $continue = $helper->ask($this->getInput(), $this->getOutput(), $question);
        }

        if ($this->getForce() || $continue) {
            $om = $this->getContainer()->get('claroline.persistence.object_manager');
            $om->startFlushSuite();
            $i = 0;

            foreach ($workspaces as $workspace) {
                $workspaceManager->deleteWorkspace($workspace);
                ++$i;
            }

            $this->getOutput()->writeln('<comment> Flushing... </comment>');
            $om->endFlushSuite();
            $om->clear();
        } else {
            //stop script here
            exit(0);
        }
    }
}
