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
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entry point of platform installation/update, ensuring that minimal requirements
 * (e.g. existing database) are met before executing operations.
 */
class PlatformInstaller implements LoggerAwareInterface
{
    use LoggableTrait;

    private $operationExecutor;
    private $baseInstaller;
    private $pluginInstaller;
    private $container;
    private $output;

    public function __construct(
        OperationExecutor $opExecutor,
        InstallationManager $baseInstaller,
        Installer $pluginInstaller,
        ContainerInterface $container
    ) {
        $this->operationExecutor = $opExecutor;
        $this->baseInstaller = $baseInstaller;
        $this->pluginInstaller = $pluginInstaller;
        $this->container = $container;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->operationExecutor->setLogger($logger);
        $this->baseInstaller->setLogger($logger);
        $this->pluginInstaller->setLogger($logger);
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
        $pluginManager = $this->container->get('claroline.manager.plugin_manager');
        $bundles = $pluginManager->getInstalledBundles();

        $operations = $this->operationExecutor->buildOperationListForBundles($bundles);
        $this->operationExecutor->execute($operations);
    }

    public function updateAll($from, $to)
    {
        $pluginManager = $this->container->get('claroline.manager.plugin_manager');
        $bundles = $pluginManager->getInstalledBundles();

        $operations = $this->operationExecutor->buildOperationListForBundles($bundles, $from, $to);

        $this->operationExecutor->execute($operations);
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
            // todo: implement a more sophisticated way to test connection, as the
            // following query works mainly in MySQL, PostgreSQL and MS-Server
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
