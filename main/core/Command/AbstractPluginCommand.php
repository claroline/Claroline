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

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\CoreBundle\Library\PluginBundle;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
abstract class AbstractPluginCommand extends Command
{
    use BaseCommandTrait;

    private $params = ['bundle' => 'the bundle name'];
    private $pluginInstaller;

    public function __construct(Installer $pluginInstaller)
    {
        $this->pluginInstaller = $pluginInstaller;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name');
    }

    protected function getPlugin(InputInterface $input, $fromKernel = true)
    {
        $bundleName = $input->getArgument('bundle');
        $kernel = $this->getApplication()->getKernel();

        if ($fromKernel) {
            return $kernel->getBundle($bundleName);
        }

        $detector = new Detector();
        $bundles = $detector->detectBundles($kernel->getProjectDir().'/vendor');

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
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $this->pluginInstaller->setLogger($consoleLogger);

        return $this->pluginInstaller;
    }

    /**
     * @todo Remove ?
     *
     * Clears the cache in production environment (mandatory after plugin
     * installation/uninstallation)
     */
    protected function resetCache(OutputInterface $output)
    {
        if ('prod' === $this->getApplication()->getKernel()->getEnvironment()) {
            $command = $this->getApplication()->get('cache:clear');

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
     */
    protected function installAssets(OutputInterface $output)
    {
        $webDir = $this->getApplication()->getKernel()->getProjectDir().'/web';
        $command = $this->getApplication()->get('assets:install');
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
