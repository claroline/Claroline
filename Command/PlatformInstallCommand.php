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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

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
            )
            ->addOption(
                'skip-assets',
                null,
                InputOption::VALUE_NONE,
                'When set to true, assets install/dump is skipped'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Installing the platform...</comment>');
        $installer = $this->getContainer()->get('claroline.installation.platform_installer');
        $installer->setOutput($output);
        $installer->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );

        if ($input->getOption('skip-assets')) {
            $installer->skipAssetsAction();
        }

        $installer->installFromKernel($input->getOption('with-optional-fixtures'));
    }
}
