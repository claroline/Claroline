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

use Claroline\BundleRecorder\Detector\Detector;
use Claroline\CoreBundle\Library\PluginBundle;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
abstract class AbstractPluginCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('bundle')) {
            $bundleName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the bundle name: ',
                function ($argument) {
                    if (empty($argument)) {
                        throw new \Exception('This argument is required');
                    }

                    return $argument;
                }
            );
            $input->setArgument('bundle', $bundleName);
        }
    }

    protected function getPlugin(InputInterface $input, $fromKernel = true)
    {
        $bundleName = $input->getArgument('bundle');
        $kernel = $this->getContainer()->get('kernel');

        if ($fromKernel) {
            return $kernel->getBundle($bundleName);
        }

        $detector = new Detector();
        $bundles = $detector->detectBundles($kernel->getRootDir().'/../vendor');

        foreach ($bundles as $bundleFqcn) {
            $parts = explode('\\', $bundleFqcn);
            $name = array_pop($parts);

            if ($name === $bundleName) {
                $bundle = new $bundleFqcn($kernel);

                if (!$bundle instanceof PluginBundle) {
                    throw new \Exception("Bundle {$bundle->getName()} must extend (Distribution)PluginBundle");
                }

                return $bundle;
            }
        }

        throw new \Exception("Cannot found bundle '{$bundleName}' in the vendor directory");
    }

    protected function getPluginInstaller(OutputInterface $output)
    {
        /** @var \Claroline\CoreBundle\Library\Installation\Plugin\Installer $installer */
        $installer = $this->getContainer()->get('claroline.plugin.installer');
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $installer->setLogger($consoleLogger);

        return $installer;
    }

    /**
     * @todo Remove ?
     *
     * Clears the cache in production environment (mandatory after plugin
     * installation/uninstallation)
     *
     * @param OutputInterface $output
     */
    protected function resetCache(OutputInterface $output)
    {
        if ($this->getContainer()->get('kernel')->getEnvironment() === 'prod') {
            $command = $this->getApplication()->find('cache:clear');

            $input = new ArrayInput(
                [
                    'command' => 'cache:clear',
                    '--no-warmup' => true,
                ]
            );

            $command->run($input, $output);
        }
    }

    /**
     * @todo Remove ?
     *
     * Refreshes the asset folder (mandatory after plugin installation/uninstallation)
     *
     * @param OutputInterface $output
     */
    protected function installAssets(OutputInterface $output)
    {
        $webDir = $this->getContainer()->getParameter('kernel.root_dir').'/../web';
        $command = $this->getApplication()->find('assets:install');
        $input = new ArrayInput(
            [
                'command' => 'assets:install',
                'target' => realpath($webDir),
                '--symlink' => true,
            ]
        );
        $command->run($input, $output);
    }
}
