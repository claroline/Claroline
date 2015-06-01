<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

/**
 * Remove unused Widget
 * Remove unused Tool
 */
class Updater050100 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Retrieve the widget
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findBy(array (
            'name' => 'innova_my_paths_widget',
        ));

        if ($widget) {
            // Delete Widget
            $em->remove($widget);
            $em->flush();
        }
    }

    private function removeWidget() {

    }
}