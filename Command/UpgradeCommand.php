<?php

namespace Claroline\MigrationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends AbstractMigrateCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:upgrade')
            ->setDescription('Upgrades a bundle to a specified version.');
    }

    protected function getAction()
    {
        return 'upgrade';
    }
}