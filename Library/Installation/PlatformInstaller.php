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

use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\SecurityBundle\Command\InitAclCommand;
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
    private $operationExecutor;
    private $baseInstaller;
    private $pluginInstaller;
    private $refresher;
    private $kernel;
    private $container;
    private $logger;
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
    )
    {
        $this->operationExecutor = $opExecutor;
        $this->baseInstaller = $baseInstaller;
        $this->pluginInstaller = $pluginInstaller;
        $this->refresher = $refresher;
        $this->kernel = $kernel;
        $this->container = $container;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
        $this->operationExecutor->setLogger($logger);
        $this->baseInstaller->setLogger($logger);
        $this->pluginInstaller->setLogger($logger);
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->refresher->setOutput($output);
    }

    public function installFromOperationFile($operationFile = null)
    {
        $this->launchPreInstallActions();

        if ($operationFile) {
            $this->operationExecutor->setOperationFile($operationFile);
        }

        $this->operationExecutor->execute();
    }

    public function installFromKernel($withOptionalFixtures = true)
    {
        $this->launchPreInstallActions();
        //The core bundle must be installed first
        $coreBundle = $this->kernel->getBundle('ClarolineCoreBundle');
        $bundles = $this->kernel->getBundles();
        $this->baseInstaller->install($coreBundle, !$withOptionalFixtures);

        foreach ($bundles as $bundle) {
            //we obviously can't install the core bundle twice.
            if ($bundle !== $coreBundle) {
                if ($bundle instanceof PluginBundle) {
                    $this->pluginInstaller->install($bundle);
                } elseif ($bundle instanceof InstallableInterface) {
                    $this->baseInstaller->install($bundle, !$withOptionalFixtures);
                }
            }
        }
    }

    private function launchPreInstallActions()
    {
        $this->createDatabaseIfNotExists();
        $this->createAclTablesIfNotExist();
        $this->createPublicSubDirectories();
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
            $code = $command->run(new ArrayInput(array()), $this->output ?: new NullOutput());

            if ($code !== 0) {
                throw new \Exception(
                    'Database cannot be created : check that the parameters you provided '
                    . 'are correct and/or that you have sufficient permissions.'
                );
            }

        }
    }

    private function createAclTablesIfNotExist()
    {
        $this->log('Checking acl tables are initialized...');
        $command = new InitAclCommand();
        $command->setContainer($this->container);
        $command->run(new ArrayInput(array()), $this->output ?: new NullOutput());
    }

    private function createPublicSubDirectories()
    {
        $this->log('Creating public sub-directories...');
        $directories = array(
            $this->container->getParameter('claroline.param.thumbnails_directory'),
            $this->container->getParameter('claroline.param.uploads_directory'),
            $this->container->getParameter('claroline.param.uploads_directory') . '/badges',
            $this->container->getParameter('claroline.param.uploads_directory') . '/logos',
            $this->container->getParameter('claroline.param.uploads_directory') . '/pictures'
        );

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory);
            }
        };
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}
