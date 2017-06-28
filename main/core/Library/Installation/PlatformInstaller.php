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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @DI\Service("claroline.installation.platform_installer")
 *
 * Entry point of platform installation/update, ensuring that minimal requirements
 * (e.g. existing database) are met before executing operations.
 */
class PlatformInstaller
{
    use LoggableTrait;

    private $operationExecutor;
    private $baseInstaller;
    private $pluginInstaller;
    private $refresher;
    private $kernel;
    private $container;
    private $output;

    /**
     * @DI\InjectParams({
     *     "opExecutor"         = @DI\Inject("claroline.installation.operation_executor"),
     *     "baseInstaller"      = @DI\Inject("claroline.installation.manager"),
     *     "pluginInstaller"    = @DI\Inject("claroline.plugin.installer"),
     *     "refresher"          = @DI\Inject("claroline.installation.refresher"),
     *     "container"          = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        OperationExecutor $opExecutor,
        InstallationManager $baseInstaller,
        Installer $pluginInstaller,
        Refresher $refresher,
        KernelInterface $kernel,
        ContainerInterface $container
    ) {
        $this->operationExecutor = $opExecutor;
        $this->baseInstaller = $baseInstaller;
        $this->pluginInstaller = $pluginInstaller;
        $this->refresher = $refresher;
        $this->kernel = $kernel;
        $this->container = $container;
        $this->bundles = parse_ini_file($this->container->getParameter('kernel.root_dir').'/config/bundles.ini');
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->operationExecutor->setLogger($logger);
        $this->baseInstaller->setLogger($logger);
        $this->pluginInstaller->setLogger($logger);
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->refresher->setOutput($output);
    }

    /**
     * Installs or updates platform packages based on the comparison
     * of local repositories versions ("vendor/composer/installed.json"
     * versus "app/config/previous-installed.json").
     */
    public function updateFromComposerInfo()
    {
        $this->launchPreInstallActions();
        $operations = $this->operationExecutor->buildOperationList();
        $this->operationExecutor->execute($operations);
    }

    public function updateAll($from, $to)
    {
        $operations = [];
        $pluginManager = $this->container->get('claroline.manager.plugin_manager');
        $bundles = $pluginManager->getInstalledBundles();

        foreach ($bundles as $bundle) {
            $operations[get_class($bundle['instance'])] = new Operation(Operation::UPDATE, $bundle['instance'], get_class($bundle['instance']));
            $operations[get_class($bundle['instance'])]->setFromVersion($from);
            $operations[get_class($bundle['instance'])]->setToVersion($to);
        }

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
            $command = new CreateDatabaseDoctrineCommand();
            $command->setContainer($this->container);
            $code = $command->run(new ArrayInput([]), $this->output ?: new NullOutput());

            if ($code !== 0) {
                throw new \Exception(
                    'Database cannot be created : check that the parameters you provided '
                    .'are correct and/or that you have sufficient permissions.'
                );
            }
        }
    }
}
