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

use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateThemeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:theme:create')
            ->setDescription('Creates a new custom theme for the platform.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the theme to create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $output->writeln('Create a new theme "'.$name.'".');

        /** @var ThemeManager $themeManager */
        $themeManager = $this->getContainer()->get('claroline.manager.theme_manager');
        $themeManager->create(['name' => $name]);

        $output->writeln('Done !');
    }
}
