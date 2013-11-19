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
use Claroline\MigrationBundle\Migrator\Migrator;

class VersionCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:version')
            ->setDescription('Displays information about the migration status of a bundle.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> displays the list of available migrations for a
bundle and marks the current installed one:

    <info>%command.name% AcmeFooBundle</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = $this->getManager($output)->getBundleStatus($this->getTargetBundle($input));

        foreach ($status[Migrator::STATUS_AVAILABLE] as $version) {
            $output->writeln(
                $version === $status[Migrator::STATUS_CURRENT] ?
                    "  * {$version} (current)" :
                    "    {$version}"
            );
        }
    }
}
