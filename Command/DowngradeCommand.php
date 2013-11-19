<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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