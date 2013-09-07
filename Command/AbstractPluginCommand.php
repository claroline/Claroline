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
     * @param string          $fqcn
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
     * @param string          $fqcn
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
}
