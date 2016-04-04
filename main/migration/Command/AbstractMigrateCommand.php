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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\MigrationBundle\Migrator\InvalidVersionException;

abstract class AbstractMigrateCommand extends AbstractCommand
{
    abstract protected function getAction();

    protected function configure()
    {
        parent::configure();
        $this->setDescription(ucfirst($this->getAction()) . 's a specified bundle')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'The target bundle version',
                Migrator::VERSION_NEAREST
            )->setHelp(<<<EOT
The <info>%command.name%</info> command {$this->getAction()}s a specified bundle:

    <info>%command.name% AcmeFooBundle</info>

By default, the {$this->getAction()} target is the nearest available version,
but you can specify a target using the <info>--target</info> option:

    <info>%command.name% AcmeFooBundle --target=YYYYMMDDHHMMSS</info>
    <info>%command.name% AcmeFooBundle --target=farthest</info>
    <info>%command.name% AcmeFooBundle --target=nearest</info>

where <info>farthest</info> means a full {$this->getAction()}.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $method = "{$this->getAction()}Bundle";
            $this->getManager($output)->{$method}(
                $this->getTargetBundle($input),
                $input->getOption('target')
            );
        } catch (InvalidVersionException $ex) {
            throw new \Exception($ex->getUsageMessage());
        }
    }
}
