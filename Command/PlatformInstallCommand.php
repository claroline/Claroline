<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;

/**
 * Performs a fresh installation of the platform based on bundles listed
 * in the application kernel.
 */
class PlatformInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:install')
            ->setDescription('Installs the platform packages listed in the application kernel.')
            ->addOption(
                'with-optional-fixtures',
                null,
                InputOption::VALUE_NONE,
                'When set to true, optional data fixtures will be loaded'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>%s - Installing the platform...</comment>', date('H:i:s')));
        $databaseCreator = new CreateDatabaseDoctrineCommand();
        $databaseCreator->setContainer($this->getContainer());
        $databaseCreator->run(new ArrayInput(array()), $output);

        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
        );
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        /** @var \Claroline\CoreBundle\Library\Installation\PlatformInstaller $installer */
        $installer = $this->getContainer()->get('claroline.installation.platform_installer');
        $installer->setOutput($output);
        $installer->setLogger($consoleLogger);
        $installer->installFromKernel($input->getOption('with-optional-fixtures'));

        $output->writeln(sprintf('<comment>%s - Platform installed.</comment>', date('H:i:s')));
    }
}
