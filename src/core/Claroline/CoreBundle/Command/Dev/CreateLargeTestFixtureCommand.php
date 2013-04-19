<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Tests\DataFixtures\BatchInsert\LoadUsersData;
use Claroline\CoreBundle\Tests\DataFixtures\BatchInsert\LoadWorkspacesData;
use Claroline\CoreBundle\Tests\DataFixtures\BatchInsert\LoadResourcesData;
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
                new InputArgument(
                    'number_user',
                    InputArgument::REQUIRED,
                    'The number of user.'
                ),
                new InputArgument(
                    'number_workspace',
                    InputArgument::REQUIRED,
                    'The number of workspace.'
                ),
                new InputArgument(
                    'number_directory',
                    InputArgument::REQUIRED,
                    'The number of directory per level.'
                ),
                new InputArgument(
                    'number_file',
                    InputArgument::REQUIRED,
                    'The number of file per level (recommanded: 5).'
                ),
                new InputArgument(
                    'depth',
                    InputArgument::REQUIRED,
                    'The depth of the data tree(s) (recommanded: 2).'
                ),
                new InputArgument(
                    'number_roots',
                    InputArgument::REQUIRED,
                    'The number of roots.'
                )
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'number_user' => 'users',
            'number_workspace' => 'workspaces',
            'number_directory' => 'number directory per level  (recommanded: 10).',
            'number_file' => 'number file per level (recommanded: 5).',
            'depth' => 'depth (recommanded: 2).',
            'number_roots' => 'numberRoots'
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
        $numberDirectory = $input->getArgument('number_directory');
        $numberFile = $input->getArgument('number_file');
        $numberRoots = $input->getArgument('number_roots');
        $depth = $input->getArgument('depth');
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
        $durationUser = $fixture->load($em);
        $output->writeln('Loading workspaces...');
        $fixture = new LoadWorkspacesData($numberWorkspace);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $durationWorkspace = $fixture->load($em);
        $output->writeln('Loading resources...');
        $fixture = new LoadResourcesData($depth, $numberFile, $numberDirectory, $numberRoots);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $durationResource = $fixture->load($em);
        $output->writeLn('********************************************************');
        $output->writeLn("Time elapsed for the user creation: " . $durationUser);
        $output->writeLn("Time elapsed for the workspace creation: " . $durationWorkspace);
        $output->writeLn("Time elapsed for the resource creation: " . $durationResource);
        $output->writeln('Done');
    }
}
