<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Command\Traits\AskRolesTrait;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Listener\Log\LogListener;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class RemoveCommand extends Command
{
    private $force = false;
    private $output = null;
    private $input = null;

    private $om;
    private $workspaceManager;
    private $finderProvider;
    private $crud;
    private $logListener;

    use AskRolesTrait;

    const BATCH_SIZE = 25;

    public function __construct(ObjectManager $om, WorkspaceManager $workspaceManager, FinderProvider $finderProvider, Crud $crud, LogListener $logListener)
    {
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->finderProvider = $finderProvider;
        $this->crud = $crud;
        $this->logListener = $logListener;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Remove workspaces');
        $this->addOption(
            'personal',
            'p',
            InputOption::VALUE_NONE,
            'When set to true, removes the personal workspaces'
        );

        $this->addOption(
            'orphans',
            'o',
            InputOption::VALUE_NONE,
            'When set to true, removes the personal workspaces of deleted users'
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $personal = $input->getOption('personal');
        $standard = $input->getOption('standard');
        $orphans = $input->getOption('orphans');
        $this->setForce($input->getOption('force'));
        $this->setInput($input);
        $this->setOutput($output);

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];

        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $this->workspaceManager->setLogger($consoleLogger);

        if ($personal) {
            $question = new ConfirmationQuestion('Remove all personal Workspaces ? y/n [y] ', true);
            $all = $helper->ask($input, $output, $question);
            $question = new ConfirmationQuestion('Include workspaces from removed users (orphans) ? y/n [y] ', true);
            $includeOrphans = $helper->ask($input, $output, $question);
            $rolesSearch = $this->askRoles($all, $input, $output, $this->om, $helper);
            $this->deletePersonalWorkspace($all, $rolesSearch, $includeOrphans);
        }

        if ($standard) {
            $question = new Question('Filter on code (continue if no filter)', null);
            $code = $helper->ask($input, $output, $question);
            $question = new Question('Filter on name (continue if no filter)', null);
            $name = $helper->ask($input, $output, $question);
            $this->deleteWorkspaceByCodeAndName($code, $name);
        }

        if ($orphans) {
            $this->deleteOrphans();
        }

        return 0;
    }

    private function deleteOrphans()
    {
        $toDelete = $this->finderProvider->fetch(Workspace::class, ['orphan' => true]);
        if (count($toDelete) > 0) {
            $this->confirmWorkspaceDelete($toDelete);
        }
    }

    private function deleteWorkspaceByCodeAndName($code, $name)
    {
        $toDelete = $this->workspaceManager->getNonPersonalByCodeAndName($code, $name);

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
        $workspacesToDelete = $all ?
            $this->workspaceManager->getPersonalWorkspaceExcludingRoles($rolesSearch, $includeOrphans, $empty, null, self::BATCH_SIZE) :
            $this->workspaceManager->getPersonalWorkspaceByRolesIncludingGroups($rolesSearch, $includeOrphans, $empty, null, self::BATCH_SIZE);

        if (count($workspacesToDelete) > 0) {
            if ($this->confirmWorkspaceDelete($workspacesToDelete)) {
                $this->deletePersonalWorkspace($all, $rolesSearch, $includeOrphans);
            }
        }
    }

    private function confirmWorkspaceDelete(array $workspaces)
    {
        $helper = $this->getHelper('question');

        foreach ($workspaces as $workspace) {
            $this->getOutput()->writeln("{$workspace->getId()}: {$workspace->getName()} - {$workspace->getCode()} ");
        }

        $continue = false;
        if (!$this->getForce()) {
            $count = count($workspaces);
            $question = new ConfirmationQuestion("Do you really want to remove theses {$count} workspaces ? y/n [y] ", true);
            $continue = $helper->ask($this->getInput(), $this->getOutput(), $question);
        }

        $this->logListener->disable();

        if ($this->getForce() || $continue) {
            $i = 1;

            $this->om->startFlushSuite();

            foreach ($workspaces as $workspace) {
                $this->getOutput()->writeln('Removing '.$i.'/'.count($workspaces));
                $this->crud->delete($workspace);
                ++$i;
            }

            $this->getOutput()->writeln('<comment> Flushing... </comment>');
            $this->om->endFlushSuite();

            return true;
        }

        return false;
    }
}
