<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Tests\DataFixtures\Special\LoadEntitiesInWorkspace;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Adds some entities (user or group) to a workspace.
 */
class WorkspaceManagementCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:management')
            ->setDescription('Will register users to the personal workspace of the specified user');

        $this->setDefinition(array(
            new InputArgument('username', InputArgument::REQUIRED, 'the username'),
            new InputArgument('count', InputArgument::OPTIONAL, 'the maximum amount of entities added'),
        ));
        $this->addOption(
            'group', 'g', InputOption::VALUE_NONE, "When set to true, the command will register groups"
        );
        $this->addOption(
            'user', 'u', InputOption::VALUE_NONE, "When set to true, the command will register groups"
        );
        $this->addOption(
            'clean', 'c', InputOption::VALUE_NONE, "When set to true, the command will register groups"
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'username',
            'count' => 'count',
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
            $output, "Enter the {$argumentName}: ", function($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    //this is not optimized
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('user')) {
            $class = 'user';
        } elseif ($input->getOption('group')) {
            $class = 'group';
        }

        $nbUsers = $input->getArgument('count');
        $username = $input->getArgument('username');
        $fixture = new LoadEntitiesInWorkspace($nbUsers, $class, $username);
        $fixture->setLogger(function ($message) use ($output){
            $output->writeln($message);
        });
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($em);
    }
}