<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Administration\Tool;

class Updater021300
{
    private $container;
    private $logger;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct($container)
    {
        $this->container     = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->log('Creating admin tools...');

        $tools = array(
            array('platform_parameters', 'icon-cog'),
            array('user_management', 'icon-user'),
            array('workspace_management', 'icon-book'),
            array('badges_management', 'icon-trophy'),
            array('registration_to_workspace', 'icon-book'),
            array('platform_plugins', 'icon-wrench'),
            array('home_tabs', 'icon-th-large'),
            array('desktop_tools', 'icon-pencil'),
            array('platform_logs', 'icon-reorder'),
            array('platform_analytics', 'icon-bar-chart'),
            array('roles_management', 'icon-group')
        );

        foreach ($tools as $tool) {
            $entity = new Tool();
            $entity ->setName($tool[0]);
            $entity ->setClass($tool[1]);
            $this->objectManager->persist($entity);
        }

        $this->objectManager->flush();
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