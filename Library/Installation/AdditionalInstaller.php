<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preInstall()
    {
        $this->createDatabaseIfNotExists();
        $this->buildDefaultTemplate();
    }

    public function postUpdate(BundleVersion $current, BundleVersion $target)
    {
        $this->createWorkspacesListWidget();
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

    private function createWorkspacesListWidget()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName('my_workspaces');

        if (is_null($workspaceWidget)) {
            $widget = new Widget();
            $widget->setName('my_workspaces');
            $widget->setConfigurable(false);
            $widget->setIcon('fake/icon/path');
            $widget->setPlugin(null);
            $widget->setExportable(false);
            $em->persist($widget);
            $em->flush();

            $widgetConfig = new DisplayConfig();
            $widgetConfig->setWidget($widget);
            $widgetConfig->setLock(false);
            $widgetConfig->setVisible(true);
            $widgetConfig->setParent(null);
            $widgetConfig->setDesktop(true);

            $em->persist($widgetConfig);
            $em->flush();
        }
    }
}
