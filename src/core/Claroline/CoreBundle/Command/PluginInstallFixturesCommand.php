<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class PluginInstallFixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:plugins:fixtures')
            ->setDescription('Install fixtures for a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginDirectory = $this->getContainer()->getParameter('claroline.param.plugin_directory');
        $output->writeln("Scanning plugin directory ('{$pluginDirectory}')...");
        $pluginFQCNs = $this->getAvailablePluginFQCNs($pluginDirectory);

        foreach ($pluginFQCNs as $pluginFQCN) {
            $sFQCN = explode('\\', $pluginFQCN);
            $vendor = $sFQCN[0];
            $bundle = $sFQCN[1];
            $kernel = $this->getApplication()->getKernel();
            $environment = $kernel->getEnvironment();

            if ($environment === 'prod' || $environment === 'dev' || $environment == 'test') {
                $fixturesPath = $kernel->getRootDir() . "/../src/plugin/{$vendor}/{$bundle}/DataFixtures";

                if ($this->isEmptyDir($fixturesPath)) {
                    $output->writeln("Loading {$environment} fixtures...");
                    $fixtureCommand = $this->getApplication()->find('doctrine:fixtures:load');
                    $fixtureInput = new ArrayInput(
                        array(
                            'command' => 'doctrine:fixtures:load',
                            '--fixtures' => $fixturesPath,
                            '--append' => true
                        )
                    );
                    $fixtureCommand->run($fixtureInput, $output);
                }
            }
        }
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

    public function isEmptyDir($folder)
    {
        if (!is_dir($folder)) {
            return false; // not a dir
        }

        $files = opendir($folder);
        while ($file = readdir($files)) {
            if ($file != '.' && $file != '..') {
                return true; // not empty
            }
        }
    }
}