<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class TestUpdateCommand extends Command
{
    use BaseCommandTrait;

    private $params = [
        'from_version' => 'from version: ',
        'to_version' => 'to version: ',
        'bundle' => 'bundle: ',
    ];

    protected function configure()
    {
        $this->setName('claroline:test_update')
            ->setAliases(['claroline:debug:update'])
            ->setDescription('Tests the local update of a bundle.');
        $this->setDefinition(
            [
                new InputArgument('bundle', InputArgument::REQUIRED, 'bundle'),
                new InputArgument('from_version', InputArgument::REQUIRED, 'from version'),
                new InputArgument('to_version', InputArgument::REQUIRED, 'to version'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $container = $this->getContainer();
        $bundleName = $input->getArgument('bundle');
        $bundle = $container->get('kernel')->getBundle($bundleName);
        $installerType = $bundle instanceof DistributionPluginBundle ?
            'Claroline\CoreBundle\Library\Installation\Plugin\Installer' :
            'claroline.installation.manager';

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        /** @var \Claroline\InstallationBundle\Manager\InstallationManager|\Claroline\CoreBundle\Library\Installation\Plugin\Installer $installer */
        $installer = $this->getContainer()->get($installerType);
        $installer->setLogger($consoleLogger);
        $from = $input->getArgument('from_version');
        $to = $input->getArgument('to_version');
        $installer->update($bundle, $from, $to);
        $installer->end($bundle, $from, $to);

        return 0;
    }
}
