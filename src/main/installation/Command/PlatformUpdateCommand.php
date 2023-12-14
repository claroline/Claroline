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

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\InstallationBundle\Manager\PlatformManager;
use Claroline\InstallationBundle\Manager\RefreshManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates, installs or uninstalls core and plugin bundles.
 */
class PlatformUpdateCommand extends Command
{
    public function __construct(
        private readonly RefreshManager $refresher,
        private readonly PlatformManager $installer,
        private readonly VersionManager $versionManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
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
                'no-theme',
                'nt',
                InputOption::VALUE_NONE,
                'Themes will not be rebuild.'
            )
            ->addOption(
                'no-refresh',
                'nr',
                InputOption::VALUE_NONE,
                'Static files will not be refreshed (build symlinks, dump assets and clears cache).'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Updaters will be executed regardless if they have been already.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        MaintenanceHandler::enableMaintenance();

        $output->writeln(
            sprintf('<comment>%s - Updating the platform...</comment>', date('H:i:s'))
        );

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

        // build themes
        if (!$input->getOption('no-theme')) {
            $this->refresher->buildThemes();
        }

        // refresh platform statics and clear cache
        if (!$input->getOption('no-refresh')) {
            $this->refresher->setOutput($output);
            $this->refresher->refresh($input->getOption('env'));
        }

        MaintenanceHandler::disableMaintenance();

        $output->writeln(
            sprintf('<comment>%s - Platform updated.</comment>', date('H:i:s'))
        );

        return 0;
    }
}
