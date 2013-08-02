<?php

namespace Claroline\MigrationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DowngradeCommand extends AbstractMigrateCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:downgrade')
            ->setDescription('Downgrades a bundle to a specified version.');
    }

    protected function getAction()
    {
        return 'downgrade';
    }
}