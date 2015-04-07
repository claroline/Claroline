<?php

namespace Innova\PathBundle\Installation;

use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\InstallationBundle\Updater\Updater;

class WidgetInstaller extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->createWidget("innova_path_widget", false, true);
        $this->createWidget("innova_my_paths_widget", true, false);

        return $this;
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
            $widget->setName($name);
            $widget->setDisplayableInDesktop($desktop);
            $widget->setDisplayableInWorkspace($workspace);
            $widget->setConfigurable(false);
            $widget->setExportable(false);
            $widget->setPlugin($plugin);
            $em->persist($widget);
            $plugin->setHasOptions(true);
            $em->persist($widget);
            $em->flush();
        }
        else {
            $this->log($name.' widget already added');
        }
    }
}
