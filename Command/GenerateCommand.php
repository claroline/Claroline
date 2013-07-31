<?php

namespace Claroline\MigrationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Twig\SqlFormatterExtension;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:migrations:generate')
            ->setDescription('Creates migration classes on a per bundle basis.');
        $this->setDefinition(
            array(new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle name'))
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('bundle')) {
            $bundleName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the bundle name: ',
                function ($argument) {
                    if (empty($argument)) {
                        throw new \Exception('This argument is required');
                    }

                    return $argument;
                }
            );
            $input->setArgument('bundle', $bundleName);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('claroline.migration.manager');
        $manager->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $manager->generateBundleMigration($input->getArgument('bundle'));
    }
}