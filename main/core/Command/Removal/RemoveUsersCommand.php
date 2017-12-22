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

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Removes users from the platform.
 */
class RemoveUsersCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 10;

    private $force = false;
    private $output = null;
    private $input = null;

    protected function configure()
    {
        $this->setName('claroline:remove:users')
            ->setDescription('Remove users');

        $this->addOption(
            'all',
            'a',
            InputOption::VALUE_NONE,
            'When set to true, removes every users'
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
        $this->setForce($input->getOption('force'));
        $this->setInput($input);
        $this->setOutput($output);

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $this->getContainer()->get('claroline.manager.user_manager')->setLogger($consoleLogger);

        $helper = $this->getHelper('question');
        //get excluding roles
        $roles = $this->getContainer()->get('claroline.persistence.object_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();
        $roleNames = array_map(function ($role) {
            return $role->getName();
        }, $roles);
        $roleNames[] = 'NONE';
        $all = $input->getOption('all');
        $questionString = $all ? 'Roles to exclude: ' : 'Roles to include: ';
        $question = new ChoiceQuestion($questionString, $roleNames);
        $question->setMultiselect(true);
        $roleNames = $helper->ask($input, $output, $question);
        $rolesSearch = array_filter($roles, function ($role) use ($roleNames) {
            return in_array($role->getName(), $roleNames);
        });

        $this->deleteUsers($all, $rolesSearch);
    }

    /**
     * batch removal. Recursive so the UOW isn't too massive.
     */
    private function deleteUsers($all, $rolesSearch)
    {
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $usersToDelete = $all ?
            $userManager->getUsersExcludingRoles($rolesSearch, null, self::BATCH_SIZE) :
            //no real good method for that one atm
            $userManager->getUsersByRolesWithGroups($rolesSearch);

        if (count($usersToDelete) > 0) {
            $this->confirmDeleteUsers($usersToDelete);
            $this->deleteUsers($all, $rolesSearch);
        }
    }

    private function confirmDeleteUsers($usersToDelete)
    {
        $helper = $this->getHelper('question');
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');

        foreach ($usersToDelete as $user) {
            $this->getOutput()->writeln("{$user->getId()}: {$user->getFirstName()} {$user->getLastName()} - {$user->getUsername()}");
        }

        if (!$this->getForce()) {
            $question = new ConfirmationQuestion('Do you really want to remove theses users ? y/n [y]', true);
            $continue = $helper->ask($this->getInput(), $this->getOutput(), $question);
        }

        if ($this->getForce() || $continue) {
            $om = $this->getContainer()->get('claroline.persistence.object_manager');
            $om->startFlushSuite();

            foreach ($usersToDelete as $user) {
                $userManager->deleteUser($user);
            }

            $om->endFlushSuite();
            $om->clear();
        } else {
            //stop script here
            exit(0);
        }
    }
}
