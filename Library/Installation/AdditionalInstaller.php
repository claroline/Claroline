<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\SecurityBundle\Command\InitAclCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    private $logger;

    public function __construct()
    {
        $self = $this;
        $this->logger = function ($message) use ($self) {
            $self->log($message);
        };
    }

    public function preInstall()
    {
        $this->createDatabaseIfNotExists();
        $this->createAclTablesIfNotExist();
        $this->buildDefaultTemplate();
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.0', '<') && version_compare($targetVersion, '2.0', '>=') ) {
            $updater020000 = new Updater\Updater020000($this->container);
            $updater020000->setLogger($this->logger);
            $updater020000->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.0', '<')  && version_compare($targetVersion, '2.0', '>=') ) {
            $updater020000 = new Updater\Updater020000($this->container);
            $updater020000->setLogger($this->logger);
            $updater020000->postUpdate();
        }

        if (version_compare($currentVersion, '2.1.2', '<')) {
            $updater020102 = new Updater\Updater020102($this->container);
            $updater020102->setLogger($this->logger);
            $updater020102->postUpdate();
        }

        if (version_compare($currentVersion, '2.1.5', '<')) {
             $this->createAclTablesIfNotExist();
        }
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
            $this->log('Unable to connect: trying to create database...');
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
        $this->log('Initializing acl tables...');
        $command = new InitAclCommand();
        $command->setContainer($this->container);
        $command->run(new ArrayInput(array()), new NullOutput());
    }

    private function buildDefaultTemplate()
    {
        $this->log('Creating default workspace template...');
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        TemplateBuilder::buildDefault($defaultTemplatePath);
    }
}
