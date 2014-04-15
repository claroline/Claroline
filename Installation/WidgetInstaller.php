<?php

namespace Innova\PathBundle\Installation;

use Claroline\CoreBundle\Entity\Widget\Widget;
use Doctrine\DBAL\Connection;

class WidgetInstaller
{

    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        
        $this->createWidget("innova_path_widget", false, true);
        $this->createWidget("innova_my_paths_widget", true, false);
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

    private function createWidget($name, $desktop, $workspace)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findBy(array('name' => $name));

        if (!$widget) {
            $this->log('adding '.$name.' widget...');

            $plugin = $em->getRepository('ClarolineCoreBundle:Plugin')
                ->findOneBy(array('vendorName' => 'Innova', 'bundleName' => 'PathBundle'));

            $widget = new Widget();
            $widget->setName('innova_path_widget');
            $widget->setDisplayableInDesktop($desktop);
            $widget->setDisplayableInWorkspace($workspace);
            $widget->setConfigurable(false);
            $widget->setExportable(false);
            $widget->setIcon('none');
            $widget->setPlugin($plugin);
            $em->persist($widget);
            $plugin->setHasOptions(true);
            $em->persist($widget);
            $em->flush();
        } else {
            $this->log($name.' widget already added');
        }
    }
}
