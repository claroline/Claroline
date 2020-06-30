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

use Claroline\CoreBundle\Library\Installation\PlatformInstaller;
use Claroline\CoreBundle\Library\Installation\Refresher;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Manager\VersionManager;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates, installs or uninstalls core and plugin bundles, based
 * on the comparison of packages previously and currently installed
 * by composer (vendor/composer/installed.json and
 * app/config/previous-installed.json).
 */
class PlatformUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('claroline:update')
            ->setDescription(
                'Updates, installs or uninstalls the platform packages brought by composer.'
            )
            ->setDefinition([
                new InputArgument('from_version', InputArgument::OPTIONAL, 'from version'),
                new InputArgument('to_version', InputArgument::OPTIONAL, 'to version'),
            ])
            ->addOption(
                'no_asset',
                'a',
                InputOption::VALUE_NONE,
                'assets:install doesn\'t execute'
            )
            ->addOption(
                'no_theme',
                't',
                InputOption::VALUE_NONE,
                'When set to true, themes won\'t be rebuilt'
            )
            ->addOption(
                'no_symlink',
                's',
                InputOption::VALUE_NONE,
                'When set to true, symlinks won\'t be rebuilt'
            )
            ->addOption(
                'no_create_database',
                'd',
                InputOption::VALUE_NONE,
                'When set to true, the create database is not executed'
            )
            ->addOption(
                'clear_cache',
                'c',
                InputOption::VALUE_NONE,
                'When set to true, the cache is cleared at the end'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Refresher $refresher */
        $refresher = $this->getContainer()->get('Claroline\CoreBundle\Library\Installation\Refresher');
        $refresher->setOutput($output);

        MaintenanceHandler::enableMaintenance();

        $output->writeln(
            sprintf('<comment>%s - Updating the platform...</comment>', date('H:i:s'))
        );

        $this->setLocale();

        if (!$input->getOption('no_create_database')) {
            $databaseCreator = new CreateDatabaseDoctrineCommand();
            $databaseCreator->setContainer($this->getContainer());
            $databaseCreator->run(new ArrayInput([]), $output);
        }

        if (!$input->getOption('no_symlink')) {
            $refresher->buildSymlinks();
        }

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        /** @var PlatformInstaller $installer */
        $installer = $this->getContainer()->get('Claroline\CoreBundle\Library\Installation\PlatformInstaller');
        $installer->setOutput($output);
        $installer->setLogger($consoleLogger);

        /** @var VersionManager $versionManager */
        $versionManager = $this->getContainer()->get('claroline.manager.version_manager');
        if ($input->getArgument('from_version') && $input->getArgument('to_version')) {
            $from = $input->getArgument('from_version');
            $to = $input->getArgument('to_version');
        } else {
            try {
                $lastVersion = $versionManager->getLatestUpgraded('ClarolineCoreBundle');
                $from = $lastVersion ? $lastVersion->getVersion() : null;
                $to = $versionManager->getCurrent();
            } catch (\Exception $e) {
                $from = null;
                $to = null;
            }
        }
        if ($from && $to) {
            $installer->updateAll($from, $to);
        } else {
            $installer->updateFromComposerInfo();
        }

        // clear cache
        if ($input->getOption('clear_cache')) {
            $refresher->clearCache($this->getContainer()->getParameter('kernel.environment'));
        }

        // dump static assets
        if (!$input->getOption('no_asset')) {
            $refresher->installAssets();
            $refresher->dumpAssets($this->getContainer()->getParameter('kernel.environment'));
        }

        // build themes
        if (!$input->getOption('no_theme')) {
            $refresher->buildThemes();
        }

        MaintenanceHandler::disableMaintenance();

        $output->writeln(
            sprintf('<comment>%s - Platform updated.</comment>', date('H:i:s'))
        );

        return 0;
    }

    private function setLocale()
    {
        $ch = $this->getContainer()->get('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $locale = $ch->getParameter('locales.default');
        if ($locale) {
            $translator = $this->getContainer()->get('translator');
            $translator->setLocale($locale);
        }
    }
}
