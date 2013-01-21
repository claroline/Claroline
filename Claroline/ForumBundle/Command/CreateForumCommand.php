<?php

namespace Claroline\ForumBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class CreateForumCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:forum:create')
            ->setDescription('Creates a forum.');
        $this->setDefinition(
            array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('name', InputArgument::REQUIRED, 'The forum name'),
                new InputArgument('subjectsAmount', InputArgument::REQUIRED, 'The number of subjects'),
                new InputArgument('messagesAmount', InputArgument::REQUIRED, 'The number of messages'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'username',
            'name' => 'name',
            'subjectsAmount' => 'subjectsAmount',
            'messagesAmount' => 'messagesAmount'
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
        $subjectsAmount = $input->getArgument('subjectsAmount');
        $messagesAmount = $input->getArgument('messagesAmount');
        $username = $input->getArgument('username');
        $name = $input->getArgument('name');
        $fixture = new LoadForumData($name, $username, $messagesAmount, $subjectsAmount);
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