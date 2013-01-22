<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Tests\DataFixtures\LoadGroupsData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Creates an group, optionaly with a specific role (default to simple group).
 */
class CreateGroupsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:groups:create')
            ->setDescription('Creates some groups with the current registerd users and roles');
        $this->setDefinition(
            array(
                new InputArgument('amount', InputArgument::REQUIRED, 'The number of groups'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'amount' => 'amount',
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
        $nbGroups = $input->getArgument('amount');
        $fixture = new LoadGroupsData($nbGroups);
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