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

use Claroline\MigrationBundle\Migrator\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:version')
            ->setDescription('Displays information about the migration status of a bundle.')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> displays the list of available migrations for a
bundle and marks the current installed one:

    <info>%command.name% AcmeFooBundle</info>

EOT
            );

        $this->addOption(
            'add',
            'a',
            InputOption::VALUE_OPTIONAL,
            'The migration timestamp to skip'
        );

        $this->addOption(
            'remove',
            'r',
            InputOption::VALUE_OPTIONAL,
            'The migration timestamp to force'
        );

        $this->addOption(
            'latest',
            'l',
            InputOption::VALUE_NONE,
            'Skip everything and set the bundle to the latest migration'
        );

        $this->addOption(
            'all',
            'o',
            InputOption::VALUE_NONE,
            'Mark all migrations as migrated'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrator = $this->getContainer()->get('claroline.migration.migrator');
        $status = $this->getManager($output)->getBundleStatus($this->getTargetBundle($input));
        $latest = $input->getOption('latest');

        if ($version = $input->getOption('remove')) {
            $migrator->markNotMigrated($this->getTargetBundle($input), $version);
        }

        if ($version = $input->getOption('add')) {
            $migrator->markMigrated($this->getTargetBundle($input), $version);
        }

        if ($version = $input->getOption('all')) {
            $migrator->markAllMigrated($this->getTargetBundle($input));
        }

        if ($latest) {
            $latest = $status[Migrator::VERSION_LATEST];
            $migrator->markMigrated($this->getTargetBundle($input), $latest);
        }

        $status = $this->getManager($output)->getBundleStatus($this->getTargetBundle($input));

        if (count($status[Migrator::STATUS_AVAILABLE]) > 0) {
            foreach ($status[Migrator::STATUS_AVAILABLE] as $version) {
                $output->writeln(
                    $version === $status[Migrator::STATUS_CURRENT] ?
                        "  * {$version} (current)" :
                        "    {$version}"
                );
            }
        } else {
            $output->writeln('No migration is available for this bundle.');
        }
    }
}
