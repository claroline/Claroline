<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Claroline\BundleRecorder\Detector;
use Claroline\CoreBundle\Library\PluginBundle;

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
        $bundles = $detector->detectBundles($kernel->getRootDir() . '/../vendor');

        foreach ($bundles as $bundleFqcn) {
            $parts = explode('\\', $bundleFqcn);
            $name = array_pop($parts);

            if ($name === $bundleName) {
                $bundle = new $bundleFqcn($kernel);

                if (!$bundle instanceof PluginBundle) {
                    throw new \Exception("Bundle {$bundle->getName()} must extend PluginBundle");
                }

                return $bundle;
            }
        }

        throw new \Exception("Cannot found bundle '{$bundleName}' in the vendor directory");
    }

    protected function getPluginInstaller(OutputInterface $output)
    {
        $installer = $this->getContainer()->get('claroline.plugin.installer');
        $installer->setLogger(function ($message) use ($output) {
            $output->writeln($message);
        });

        return $installer;
    }

    /**
     * @todo Remove ?
     *
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
     * @todo Remove ?
     * 
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
