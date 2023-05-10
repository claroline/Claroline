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

use Claroline\MigrationBundle\Migrator\InvalidVersionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceCommand extends AbstractMigrateCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Replace the last migration of a bundle (this is equivalent to downgrade => discard => generate => upgrade)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager = $this->getManager($output);
        try {
            $manager->downgradeBundle($this->getTargetBundle($input), $input->getOption('target'));
            $manager->discardUpperMigrations($this->getTargetBundle($input));
            $manager->generateBundleMigration($this->getTargetBundle($input));
            $manager->upgradeBundle($this->getTargetBundle($input), $input->getOption('target'));
        } catch (InvalidVersionException $ex) {
            throw new \Exception($ex->getUsageMessage());
        }

        return 0;
    }

    protected function getAction(): string
    {
        return 'replace';
    }
}
