<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Claroline\InstallationBundle\Bundle\BundleVersion;

class AdditionalInstaller extends BaseInstaller
{
    public function preInstall()
    {
        $this->createDatabaseIfNotExists();
        $this->buildDefaultTemplate();
    }

    public function preUpdate(BundleVersion $current, BundleVersion $target)
    {
        $updater002000005 = new Updater\Updater002000005($this->container);
                
        if (version_compare($current->getVersion(), '1.3', '>') && version_compare($target->getVersion(), '2.1', '<') ) {
            $updater002000005->preUpdate();
        }
    }
    
    public function postUpdate(BundleVersion $current, BundleVersion $target)
    {
        $updater002000005 = new Updater\Updater002000005($this->container);
                
        if (version_compare($current->getVersion(), '1.3', '>')  && version_compare($target->getVersion(), '2.1', '<') ) {
            $updater002000005->postUpdate();
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

    private function buildDefaultTemplate()
    {
        $this->log('Creating default workspace template...');
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        TemplateBuilder::buildDefault($defaultTemplatePath);
    }
}