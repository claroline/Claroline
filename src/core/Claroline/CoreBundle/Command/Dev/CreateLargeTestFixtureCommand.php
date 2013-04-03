<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Tests\DataFixtures\BatchInsert\LoadUsersData;
use Claroline\CoreBundle\Tests\DataFixtures\BatchInsert\LoadWorkspacesData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateLargeTestFixtureCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:fixture:load')
            ->setDescription('Fills the database with a large amount of data.');
        $this->setDefinition(
            array(
                new InputArgument('number_user', InputArgument::REQUIRED, 'The number of user.'),
                new InputArgument('number_workspace', InputArgument::REQUIRED, 'The number of workspace.')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'number_user' => 'users',
            'number_workspace' => 'workspaces'
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
            "how many {$argumentName}: ",
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
        $numberUser = $input->getArgument('number_user');
        $numberWorkspace = $input->getArgument('number_workspace');
        $output->writeln('Loading fixtures...');
        $output->writeln('Loading users...');
        $fixture = new LoadUsersData($numberUser);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $fixture->load($em);
        $output->writeln('Loading workspaces...');
        $fixture = new LoadWorkspacesData($numberWorkspace);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $fixture->load($em);
        $output->writeln('Done');
    }
}
