<?php

namespace Claroline\CoreBundle\Command;

use \InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
abstract class AbstractPluginCommand extends ContainerAwareCommand
{
    /**
     * Installs a plugin using the claroline plugin installer.
     *
     * @param string $fqcn
     * @param OutputInterface $output
     *
     * @return boolean True if the installation succeed, false otherwise
     */
    protected function installPlugin($fqcn, OutputInterface $output)
    {
        $installer = $this->getContainer()->get('claroline.plugin.installer');

        if (!$installer->isInstalled($fqcn)) {
            $output->writeln("Installing plugin '{$fqcn}'...");
            $installer->install($fqcn);
            $output->writeln('Done');

            return true;
        }

        $output->writeln("Plugin '{$fqcn}' is already installed.");

        return false;
    }

    /**
     * Uninstalls a plugin using the claroline plugin installer.
     *
     * @param string $fqcn
     * @param OutputInterface $output
     *
     * @return boolean True if the uninstallation succeed, false otherwise
     */
    protected function uninstallPlugin($fqcn, OutputInterface $output)
    {
        $installer = $this->getContainer()->get('claroline.plugin.installer');

        if ($installer->isInstalled($fqcn)) {
            $output->writeln("Uninstalling plugin '{$fqcn}'...");
            $installer->uninstall($fqcn);
            $output->writeln('Done');

            return true;
        }

        $output->writeln("Plugin '{$fqcn}' is not installed.");

        return false;
    }

    /**
     * Helper method parsing the plugin directory and applying the "installPlugin"
     * or "uninstallPlugin" methods of this class on each plugin found.
     *
     * @param string $methodName "installPlugin" or "uninstallPlugin"
     * @param OutputInterface $output
     *
     * @return boolean True if the callback method has succeeded at least once, false otherwise
     */
    protected function walkPluginDirectory($methodName, OutputInterface $output)
    {
        if ($methodName != 'installPlugin' && $methodName != 'uninstallPlugin') {
            throw new InvalidArgumentException(
                "First parameter must be either 'installPlugin' "
                . " or 'uninstallPlugin', {$methodName} given."
            );
        }

        $pluginDirectory = $this->getContainer()->getParameter('claroline.param.plugin_directory');
        $hasEffect = false;
        $output->writeln("Scanning plugin directory ('{$pluginDirectory}')...");
        $pluginFQCNs = $this->getAvailablePluginFQCNs($pluginDirectory);

        foreach ($pluginFQCNs as $pluginFQCN) {
            if ($this->{$methodName}($pluginFQCN, $output)) {
                $hasEffect = true;
            }
        }

        return $hasEffect;
    }

    /**
     * Clears the cache in production environment (mandatory after plugin
     * installation/uninstallation).
     *
     * @param OutputInterface $output
     */
    protected function resetCache(OutputInterface $output)
    {
        if ($this->getContainer()->get('kernel')->getEnvironment() === 'prod') {
            $command = $this->getApplication()->find('cache:clear');

            $input = new ArrayInput(
                array(
                    'command' => 'cache:clear',
                    '--no-warmup' => true,
                )
            );

            $command->run($input, $output);
        }
    }

    /**
     * Refreshes the asset folder (mandatory after plugin installation/uninstallation)
     *
     * @param OutputInterface $output
     */
    protected function installAssets(OutputInterface $output)
    {
        $command = $this->getApplication()->find('assets:install');
        $input = new ArrayInput(
            array(
                'command' => 'assets:install',
                'target' => realpath(__DIR__ . '/../../../../../web'),
                '--symlink' => true
            )
        );
        $command->run($input, $output);
    }

    private function getAvailablePluginFQCNs($pluginDirectory)
    {
        $fqcns = array();
        $pluginVendors = new \DirectoryIterator($pluginDirectory);

        foreach ($pluginVendors as $vendor) {
            if (!$vendor->isDir() || $vendor->isDot()) {
                continue;
            }

            $vendorName = $vendor->getBasename();
            $vendorPlugins = new \DirectoryIterator($vendor->getPathname());

            foreach ($vendorPlugins as $plugin) {
                if (!$plugin->isDir() || $plugin->isDot()) {
                    continue;
                }

                $bundleName = $plugin->getBasename();
                $fqcns[] = "{$vendorName}\\{$bundleName}\\{$vendorName}{$bundleName}";
            }
        }

        return $fqcns;
    }
}