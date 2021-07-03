<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Command;

use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\KernelBundle\Bundle\PluginBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
abstract class AbstractPluginCommand extends Command
{
    protected $pluginInstaller;

    public function __construct(Installer $pluginInstaller)
    {
        $this->pluginInstaller = $pluginInstaller;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name');
    }

    protected function getPlugin(InputInterface $input)
    {
        $bundleName = $input->getArgument('bundle');
        $kernel = $this->getApplication()->getKernel();

        $bundle = $kernel->getBundle($bundleName);
        if (empty($bundle)) {
            throw new \Exception("Cannot found bundle '{$bundleName}' in the bundles.ini");
        }

        if (!$bundle instanceof PluginBundle) {
            throw new \Exception("Bundle {$bundle->getName()} must extend (Distribution)PluginBundle");
        }

        return $bundle;
    }

    /**
     * Clears the cache (mandatory after plugin installation/uninstallation).
     */
    protected function resetCache(OutputInterface $output)
    {
        $command = $this->getApplication()->get('cache:clear');

        $input = new ArrayInput([
            'command' => 'cache:clear',
            '--no-warmup' => true,
        ]);

        $command->run($input, $output);
    }
}
