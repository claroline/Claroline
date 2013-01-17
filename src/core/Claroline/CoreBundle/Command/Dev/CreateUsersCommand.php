<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUsersData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:users:create')
            ->setDescription('Creates a lot of users.');
        $this->setDefinition(array(
            new InputArgument('amount', InputArgument::REQUIRED, 'The number of users created'),
        ));
        $this->addOption(
            'ws_creator',
            'wsc',
            InputOption::VALUE_NONE,
            'When set to true, created users will have the workspace creator role'
        );
        $this->addOption(
            'admin',
            'a',
            InputOption::VALUE_NONE,
            'When set to true, created users will have the admin role'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'amount' => 'the number of users'
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('admin')) {
            $role = 'admin';
        } elseif ($input->getOption('ws_creator')) {
            $role = 'ws_creator';
        } else {
            $role = 'user';
        }
        $nbUsers = $input->getArgument('amount');
        $fixture = new LoadUsersData($nbUsers, $role);
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