<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

/**
 * Remove unused Widget
 * Remove unused Tool.
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
        $this->removeWidget();

        $this->removeTool();
    }

    private function removeWidget()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Retrieve the widget
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneBy([
            'name' => 'innova_my_paths_widget',
        ]);

        if ($widget) {
            // Delete Widget
            $em->remove($widget);
            $em->flush();
        }
    }

    private function removeTool()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Retrieve the tool
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy([
            'name' => 'innova_path',
        ]);

        if ($tool) {
            // Delete Widget
            $em->remove($tool);
            $em->flush();
        }
    }
}
