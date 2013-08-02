<?php

namespace Claroline\MigrationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:generate')
            ->setDescription('Creates migration classes on a per bundle basis.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command generates migration classes for a
specified bundle:

    <info>%command.name% AcmeFooBundle</info>

Migrations classes are generated for all the default drivers's platforms in
the <info>Migrations</info> directory of the bundle.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getManager($output)->generateBundleMigration($input->getArgument('bundle'));
    }
}