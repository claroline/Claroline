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

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\SecurityBundle\Command\InitAclCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.installation.platform_installer")
 *
 * Entry point of platform installation/update, ensuring that the minimal
 * environment (e.g. existing database) is set up before executing operations.
 */
class PlatformInstaller
{
    private $operationExecutor;
    private $container;
    private $logger;

    /**
     * @DI\InjectParams({
     *     "opExecutor" = @DI\Inject("claroline.installation.operation_executor"),
     *     "container"  = @DI\Inject("service_container")
     * })
     */
    public function __construct(OperationExecutor $opExecutor, ContainerInterface $container)
    {
        $this->operationExecutor = $opExecutor;
        $this->container = $container;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function installFromOperationFile($operationFile = null)
    {
        $this->launchPreInstallActions();

        if ($operationFile) {
            $this->operationExecutor->setOperationFile($operationFile);
        }

        if ($this->logger) {
            $this->operationExecutor->setLogger($this->logger);
        }

        $this->operationExecutor->execute();
    }

    private function launchPreInstallActions()
    {
        $this->createDatabaseIfNotExists();
        $this->createAclTablesIfNotExist();
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
            $code = $command->run(new ArrayInput(array()), new NullOutput());

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
        $command->run(new ArrayInput(array()), new NullOutput());
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}
