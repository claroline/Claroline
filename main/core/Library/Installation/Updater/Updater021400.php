<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\InstallationBundle\Updater\Updater;

class Updater021400 extends Updater
{
    private $container;
    private $oldCachePath;
    private $newCachePath;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct($container)
    {
        $this->container = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
        $ds = DIRECTORY_SEPARATOR;
        $this->oldCachePath = $container
            ->getParameter('kernel.root_dir').$ds.'cache'.$ds.'claroline.cache.php';
        $this->newCachePath = $container
                ->getParameter('kernel.root_dir').$ds.'cache'.$ds.'claroline.cache.ini';
    }

    public function postUpdate()
    {
        $this->log('Updating cache...');
        $this->container->get('claroline.manager.cache_manager')->refresh();
        $this->log('Removing old cache...');

        if (file_exists($this->oldCachePath)) {
            unlink($this->oldCachePath);
        }

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
            array('roles_management', 'icon-group'),
        );

        $existingTools = $this->objectManager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findAll();

        if (count($existingTools) === 0) {
            foreach ($tools as $tool) {
                $entity = new AdminTool();
                $entity->setName($tool[0]);
                $entity->setClass($tool[1]);
                $this->objectManager->persist($entity);
            }
        }

        $this->objectManager->flush();
    }
}
