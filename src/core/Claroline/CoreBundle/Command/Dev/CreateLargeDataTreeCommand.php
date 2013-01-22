<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTreeData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Creates a large resource tree
 */
class CreateLargeDataTreeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:datatree:create')
            ->setDescription('Creates a tree of resources. For better perfs, launch with --env=prod.');
        $this->setDefinition(
            array(
                new InputArgument(
                    'username',
                    InputArgument::REQUIRED,
                    'The user creating the tree'
                ),
                new InputArgument(
                    'depth',
                    InputArgument::REQUIRED,
                    'The number of levels'
                ),
                new InputArgument(
                    'directory_count',
                    InputArgument::REQUIRED,
                    'The number of directories per level (min 1)'
                ),
                new InputArgument(
                    'file_count',
                    InputArgument::REQUIRED,
                    'The number of files per level'
                ),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'username',
            'depth' => 'depth',
            'directory_count' => 'number of directories per level',
            'file_count' => 'number of files per level'
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
        $username = $input->getArgument('username');
        $maxDepth = $input->getArgument('depth');
        $directoryCount = $input->getArgument('directory_count');
        $fileCount = $input->getArgument('file_count');
        $fixture = new LoadResourceTreeData($username, $maxDepth, $directoryCount, $fileCount);
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