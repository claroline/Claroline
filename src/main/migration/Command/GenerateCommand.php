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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends AbstractCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(
            'output',
            null,
            InputOption::VALUE_REQUIRED,
            'The bundle output if you want migrations to be generated somewhere else'
        );
        $this
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getManager($output)->generateBundleMigration($this->getTargetBundle($input));

        return 0;
    }
}
