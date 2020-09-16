<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Command;

use Claroline\ThemeBundle\Manager\ThemeBuilderManager;
use Claroline\ThemeBundle\Manager\ThemeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildThemesCommand extends Command
{
    private $themeBuilder;
    private $themeManager;

    public function __construct(ThemeBuilderManager $themeBuilder, ThemeManager $themeManager)
    {
        $this->themeBuilder = $themeBuilder;
        $this->themeManager = $themeManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Build themes which are installed in the platform')
            ->addOption('theme', 't', InputOption::VALUE_OPTIONAL, 'Theme name. Rebuild only this theme.')
            ->addOption('no-cache', 'c', InputOption::VALUE_NONE, 'Rebuild themes without using cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Rebuilding themes...');

        // Get themes to build (either a single theme or all themes)
        $themeName = $input->getOption('theme');
        if (!empty($themeName)) {
            $theme = $this->themeManager->getThemeByName($themeName);
            if (!empty($theme)) {
                $themesToRebuild = [$this->themeManager->getThemeByName($themeName)];
            } else {
                $output->writeln('Can not find theme "'.$themeName.'".');
            }
        } else {
            $themesToRebuild = $this->themeManager->all();
        }

        if (!empty($themesToRebuild)) {
            $logs = $this->themeBuilder->rebuild(
                $themesToRebuild,
                !$input->getOption('no-cache')
            );

            foreach ($logs as $themeName => $themeLogs) {
                $output->writeln('Theme: '.$themeName);
                foreach ($themeLogs as $log) {
                    $output->writeln($log);
                }
            }
        }

        $output->writeln('Done !');

        return 0;
    }
}
