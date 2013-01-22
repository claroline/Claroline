<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Tests\DataFixtures\LoadMessagesData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class SendMessagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:messages:create')
            ->setDescription('Creates some messages sent between users');
        $this->setDefinition(
            array(
                new InputArgument('username', InputArgument::REQUIRED, 'The user'),
                new InputArgument('amount', InputArgument::REQUIRED, 'The number of messages'),
                new InputArgument('action', InputArgument::REQUIRED, 'The action ')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'user',
            'amount' => 'amount',
            'action' => 'action'
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
            "Enter the {$argumentName}: ",
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
        $nbMessages = $input->getArgument('amount');
        $username = $input->getArgument('username');
        $action = $input->getArgument('action');
        $usernames = array();
        $action == 's' ? $usernames['from'] = $username : $usernames['to'] = $username;

        $fixture = new LoadMessagesData($usernames, $nbMessages);
        $fixture->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($em);
    }
}