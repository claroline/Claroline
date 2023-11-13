<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Entry point of platform installation/update, ensuring that minimal requirements
 * (e.g. existing database) are met before executing operations.
 */
class PlatformManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private OutputInterface $output;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly KernelInterface $kernel,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly BundleManager $baseInstaller,
        private readonly PluginManager $pluginInstaller
    ) {
    }

    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void
    {
        $this->baseInstaller->setShouldReplayUpdaters($shouldReplayUpdaters);
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Installs platform packages based on the bundles configuration (INI file).
     */
    public function installAll(): void
    {
        // make sure the DB is created
        $this->createDatabaseIfNotExists();

        // generate platform_options with default parameters if it does not exist
        $this->config->saveParameters();

        // get all the plugins available in the platform (even the ones which are disabled)
        $bundles = $this->getInstallableBundles();

        $this->execute($bundles);
        $this->end($bundles);
    }

    public function updateAll(string $from, string $to): void
    {
        // generate platform_options with default parameters if it does not exist
        $this->config->saveParameters();

        // get all the plugins available in the platform (even the ones which are disabled)
        $bundles = $this->getInstallableBundles();

        $this->execute($bundles, $from, $to);
        $this->end($bundles, $from, $to);
    }

    public function execute(array $bundles, ?string $fromVersion = null, ?string $toVersion = null): void
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

    private function end(array $bundles, ?string $fromVersion = null, ?string $toVersion = null): void
    {
        $this->logger->info('Ending operations...');

        foreach ($bundles as $bundle) {
            $this->pluginInstaller->end($bundle, $fromVersion, $toVersion);
        }
    }

    private function getInstallableBundles(): array
    {
        // during the installation/update process all the available bundles are loaded in the kernel
        // @see Claroline\KernelBundle\Kernel::registerBundles()
        return array_filter($this->kernel->getBundles(), function ($bundle) {
            return $bundle instanceof InstallableInterface;
        });
    }

    private function isBundleAlreadyInstalled(string $bundleFqcn, bool $checkCoreBundle = true): bool
    {
        if ('Claroline\CoreBundle\ClarolineCoreBundle' === $bundleFqcn && !$checkCoreBundle) {
            return true;
        }

        try {
            return !empty($this->om->getRepository(Plugin::class)->findOneByBundleFQCN($bundleFqcn));
        } catch (TableNotFoundException $e) {
            // we're probably installing the platform because the database isn't here yet do... return false
            return false;
        }
    }

    private function createDatabaseIfNotExists(): void
    {
        try {
            $this->logger->info('Checking database connection...');

            $cn = $this->container->get('doctrine.dbal.default_connection');
            // see http://stackoverflow.com/questions/3668506/efficient-sql-test-query-or-validation-query-that-will-work-across-all-or-most
            $cn->query('SELECT 1');
        } catch (\Exception $ex) {
            $this->logger->notice('Unable to connect to database: trying to create database...');

            $command = new CreateDatabaseDoctrineCommand($this->container->get('doctrine'));
            $code = $command->run(new ArrayInput([]), $this->output ?: new NullOutput());

            if (0 !== $code) {
                throw new \Exception('Database cannot be created : check that the parameters you provided are correct and/or that you have sufficient permissions.');
            }
        }
    }
}
