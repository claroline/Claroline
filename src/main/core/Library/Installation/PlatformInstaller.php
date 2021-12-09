<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Entry point of platform installation/update, ensuring that minimal requirements
 * (e.g. existing database) are met before executing operations.
 */
class PlatformInstaller implements LoggerAwareInterface
{
    use LoggableTrait;

    private $kernel;
    private $pluginManager;
    private $pluginInstaller;
    private $om;
    private $baseInstaller;
    private $container;
    private $output;

    public function __construct(
        KernelInterface $kernel,
        PluginManager $pluginManager,
        Installer $pluginInstaller,
        ObjectManager $om,
        InstallationManager $baseInstaller,
        ContainerInterface $container
    ) {
        $this->kernel = $kernel;
        $this->pluginManager = $pluginManager;
        $this->pluginInstaller = $pluginInstaller;
        $this->om = $om;
        $this->baseInstaller = $baseInstaller;
        $this->container = $container;
    }

    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void
    {
        $this->baseInstaller->setShouldReplayUpdaters($shouldReplayUpdaters);
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Installs platform packages based on the bundles configuration (INI file).
     */
    public function installAll()
    {
        $this->launchPreInstallActions();

        $bundles = $this->getInstallableBundles();

        $this->execute($bundles);
        $this->end($bundles);
    }

    public function updateAll($from, $to)
    {
        $bundles = $this->getInstallableBundles();

        $this->execute($bundles, $from, $to);
        $this->end($bundles, $from, $to);
    }

    public function execute(array $bundles, ?string $fromVersion = null, ?string $toVersion = null)
    {
        $isFreshInstall = !$fromVersion && !$toVersion;

        foreach ($bundles as $bundle) {
            $bundleFqcn = get_class($bundle);
            // If plugin is installed, update it. Otherwise, install it.
            if (!$isFreshInstall && $this->isBundleAlreadyInstalled($bundleFqcn, false)) {
                $this->pluginInstaller->update($bundle, $fromVersion, $toVersion);
            } else {
                $this->pluginInstaller->install($bundle);
            }
        }
    }

    private function end(array $bundles, ?string $fromVersion = null, ?string $toVersion = null)
    {
        $this->log('Ending operations...');

        $isFreshInstall = !$fromVersion && !$toVersion;

        foreach ($bundles as $bundle) {
            if ($isFreshInstall) {
                $this->pluginInstaller->end($bundle);
            } else {
                $this->pluginInstaller->end($bundle, $fromVersion, $toVersion);
            }
        }
    }

    private function getInstallableBundles(): array
    {
        // during the install/update process all the available bundles are loaded in the kernel
        // @see Claroline\KernelBundle\Kernel::registerBundles()
        return array_filter($this->kernel->getBundles(), function ($bundle) {
            return $bundle instanceof InstallableInterface;
        });
    }

    private function isBundleAlreadyInstalled($bundleFqcn, $checkCoreBundle = true)
    {
        if ('Claroline\CoreBundle\ClarolineCoreBundle' === $bundleFqcn && !$checkCoreBundle) {
            return true;
        }

        try {
            return $this->om->getRepository('ClarolineCoreBundle:Plugin')->findOneByBundleFQCN($bundleFqcn);
        } catch (TableNotFoundException $e) {
            // we're probably installing the platform because the database isn't here yet do... return false
            return false;
        }
    }

    private function launchPreInstallActions()
    {
        $this->createDatabaseIfNotExists();
    }

    private function createDatabaseIfNotExists()
    {
        try {
            $this->log('Checking database connection...');
            $cn = $this->container->get('doctrine.dbal.default_connection');

            // see http://stackoverflow.com/questions/3668506/efficient-sql-test-query-or-validation-query-that-will-work-across-all-or-most
            $cn->query('SELECT 1');
        } catch (\Exception $ex) {
            $this->log('Unable to connect to database: trying to create database...');
            $command = new CreateDatabaseDoctrineCommand($this->container->get('doctrine'));
            $code = $command->run(new ArrayInput([]), $this->output ?: new NullOutput());

            if (0 !== $code) {
                throw new \Exception('Database cannot be created : check that the parameters you provided are correct and/or that you have sufficient permissions.');
            }
        }
    }
}
