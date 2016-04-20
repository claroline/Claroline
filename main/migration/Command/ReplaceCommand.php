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
use Symfony\Component\Console\Input\InputOption;

class ReplaceCommand extends AbstractMigrateCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:migration:replace')
            ->setDescription('Replace the last migration of a bundle (this is equivalent to downgrade => discard => generate => upgrade)');
        $this->addOption(
            'output',
            null,
            InputOption::VALUE_REQUIRED,
            'The bundle output if you want migrations to be generated somewhere else'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getManager($output);
        try {
            $manager->downgradeBundle($this->getTargetBundle($input), $input->getOption('target'));
            $manager->discardUpperMigrations($this->getTargetBundle($input));
            $manager->generateBundleMigration($this->getTargetBundle($input), $this->getOutputBundle($input));
            $manager->upgradeBundle($this->getTargetBundle($input), $input->getOption('target'));
        } catch (InvalidVersionException $ex) {
            throw new \Exception($ex->getUsageMessage());
        }
    }

    protected function getAction()
    {
        return 'replace';
    }
}
