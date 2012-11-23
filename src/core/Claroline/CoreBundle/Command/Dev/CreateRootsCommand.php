<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceRootsData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Creates many resource roots (workspaces).
 */
class CreateRootsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:roots:create')
            ->setDescription('Creates new roots.');
        $this->setDefinition(array(
            new InputArgument('username', InputArgument::REQUIRED, 'The user creating the roots'),
            new InputArgument('count', InputArgument::REQUIRED, 'The number of roots'),
        ));
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $amount = $input->getArgument('count');
        $fixture = new LoadResourceRootsData($username, $amount);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($em);
    }
}