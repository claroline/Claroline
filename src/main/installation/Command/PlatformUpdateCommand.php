<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Command;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Installation\PlatformInstaller;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\InstallationBundle\Manager\RefreshManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Updates, installs or uninstalls core and plugin bundles.
 */
class PlatformUpdateCommand extends Command
{
    private $refresher;
    private $installer;
    private $versionManager;
    private $platformConfigurationHandler;
    private $translator;
    private $environment;

    public function __construct(
        RefreshManager $refresher,
        PlatformInstaller $installer,
        VersionManager $versionManager,
        PlatformConfigurationHandler $platformConfigurationHandler,
        TranslatorInterface $translator,
        string $environment
    ) {
        $this->refresher = $refresher;
        $this->installer = $installer;
        $this->versionManager = $versionManager;
        $this->platformConfigurationHandler = $platformConfigurationHandler;
        $this->translator = $translator;
        $this->environment = $environment;

        parent::__construct();
    }

    protected function configure()
    {
        $this
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
                'clear_cache',
                'c',
                InputOption::VALUE_NONE,
                'When set to true, the cache is cleared at the end'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'When set to true, updaters will be executed regardless if they have been already.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->refresher->setOutput($output);

        MaintenanceHandler::enableMaintenance();

        $output->writeln(
            sprintf('<comment>%s - Updating the platform...</comment>', date('H:i:s'))
        );

        // generate platform_options with default parameters if it does not exist
        $this->platformConfigurationHandler->saveParameters();

        $this->setLocale();

        if (!$input->getOption('no_symlink')) {
            $this->refresher->buildSymlinks();
        }

        if ($input->getOption('force')) {
            $this->installer->setShouldReplayUpdaters(true);
        }

        $this->installer->setOutput($output);

        if ($input->getArgument('from_version') && $input->getArgument('to_version')) {
            $from = $input->getArgument('from_version');
            $to = $input->getArgument('to_version');
        } else {
            try {
                $lastVersion = $this->versionManager->getLatestUpgraded('ClarolineCoreBundle');
                $from = $lastVersion ? $lastVersion->getVersion() : null;
                $to = $this->versionManager->getCurrent();
            } catch (\Exception $e) {
                $from = null;
                $to = null;
            }
        }
        if ($from && $to) {
            $this->installer->updateAll($from, $to);
        } else {
            $this->installer->installAll();
        }

        // dump static assets
        if (!$input->getOption('no_asset')) {
            $this->refresher->installAssets();
            $this->refresher->dumpAssets();
        }

        // build themes
        if (!$input->getOption('no_theme')) {
            $this->refresher->buildThemes();
        }

        // clear cache
        if ($input->getOption('clear_cache')) {
            $this->refresher->clearCache($this->environment);
        }

        MaintenanceHandler::disableMaintenance();

        $output->writeln(
            sprintf('<comment>%s - Platform updated.</comment>', date('H:i:s'))
        );

        return 0;
    }

    private function setLocale()
    {
        $locale = $this->platformConfigurationHandler->getParameter('locales.default');
        if ($locale) {
            $this->translator->setLocale($locale);
        }
    }
}
