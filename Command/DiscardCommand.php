<?php

namespace Claroline\MigrationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscardCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:discard')
            ->setDescription('Deletes migration classes which are above the current version of the bundle.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command deletes bundle migration classes which
are above the bundle's current version:

    <info>%command.name% AcmeFooBundle</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getManager($output)->discardUpperMigrations($this->getTargetBundle($input));
    }
}