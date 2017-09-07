<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Theme;

use Claroline\CoreBundle\Manager\Theme\ThemeBuilderManager;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildThemesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:theme:build')
            ->setDescription('Build themes which are installed in the platform')
            ->addOption('theme',    't',  InputOption::VALUE_OPTIONAL, 'Theme name. Rebuild only this theme.')
            ->addOption('no-cache', 'nc', InputOption::VALUE_NONE,     'Rebuild themes without using cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ThemeManager $themeManager */
        $themeManager = $this->getContainer()->get('claroline.manager.theme_manager');
        /** @var ThemeBuilderManager $builder */
        $builder = $this->getContainer()->get('claroline.manager.theme_builder');

        $output->writeln('Rebuilding themes...');

        // Get themes to build (either a single theme or all themes)
        $themeName = $input->getOption('theme');
        if (!empty($themeName)) {
            $theme = $themeManager->getThemeByName($themeName);
            if (!empty($theme)) {
                $themesToRebuild = [$themeManager->getThemeByName($themeName)];
            } else {
                $output->writeln('Can not find theme "'.$themeName.'".');
            }
        } else {
            $themesToRebuild = $themeManager->all();
        }

        if (!empty($themesToRebuild)) {
            $logs = $builder->rebuild(
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
    }
}
