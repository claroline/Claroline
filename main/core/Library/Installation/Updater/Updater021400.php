<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
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

        $tools = [
            ['platform_parameters', 'icon-cog'],
            ['user_management', 'icon-user'],
            ['workspace_management', 'icon-book'],
            ['badges_management', 'icon-trophy'],
            ['registration_to_workspace', 'icon-book'],
            ['platform_plugins', 'icon-wrench'],
            ['home_tabs', 'icon-th-large'],
            ['desktop_tools', 'icon-pencil'],
            ['platform_logs', 'icon-reorder'],
            ['platform_analytics', 'icon-bar-chart'],
            ['roles_management', 'icon-group'],
        ];

        $existingTools = $this->objectManager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findAll();

        if (0 === count($existingTools)) {
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
