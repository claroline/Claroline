<?php

namespace Claroline\CoreBundle\Command\Dev;

use Claroline\CoreBundle\Library\PluginBundle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;

class TestUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:test_update')
            ->setDescription('Tests the local update of a bundle.');
        $this->setDefinition(
            array(
                new InputArgument('from_version', InputArgument::REQUIRED, 'from version'),
                new InputArgument('to_version', InputArgument::REQUIRED, 'to version'),
                new InputArgument('bundle', InputArgument::REQUIRED, 'bundle')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'from_version' => 'from version: ',
            'to_version' => 'to version: ',
            'bundle' => 'bundle: '
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
            $argumentName,
            function ($argument) {
                if ($argument === null) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundleName = $input->getArgument('bundle');
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);
        $installerType = $bundle instanceof PluginBundle ?
            'claroline.plugin.installer' :
            'claroline.installation.manager';
        $installer = $this->getContainer()->get($installerType);
        $installer->setLogger(function ($message) use ($output) {
            $output->writeln($message);
        });
        $from = $input->getArgument('from_version');
        $to = $input->getArgument('to_version');
        $installer->update($bundle, $from, $to);
    }
}


