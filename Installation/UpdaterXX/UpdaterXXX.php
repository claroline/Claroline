<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\CoreBundle\Entity\Widget\Widget;
use Doctrine\DBAL\Connection;

class Updater020200
{

    private $container;
    private $logger;
    /** @var  Connection */
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function preUpdate()
    {
        
    }

    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');


        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findBy(array('name' => 'claroline_path_widget'));

        if (!$widget) {
            $this->log('adding the path widget...');

            $plugin = $em->getRepository('ClarolineCoreBundle:Plugin')
                ->findOneBy(array('vendorName' => 'Innova', 'bundleName' => 'PathBundle'));

            $widget = new Widget();
            $widget->setName('claroline_path_widget');
            $widget->setDisplayableInDesktop(false);
            $widget->setDisplayableInWorkspace(true);
            $widget->setConfigurable(false);
            $widget->setExportable(false);
            $widget->setIcon('none');
            $widget->setPlugin($plugin);
            $em->persist($widget);
            $plugin->setHasOptions(true);
            $em->persist($widget);
            $em->flush();
        } else {
            $this->log('path widget already added');
        }
    }


    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}