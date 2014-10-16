<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030601
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->mm = $container->get('claroline.manager.mask_manager');
    }

    public function postUpdate()
    {
        $this->updatePermissionMasksAndMenus();
    }

    private updatePermissionMasksAndMenus()
    {
        $this->log('Updating resource permissions...');
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        foreach ($resourceTypes as $resourceType) {
            //we'll create a mask for each resource type
            $permissionMap = $this->mm->getPermissionMap($resourceType);
            $maskDecoder = new MaskDecoder();
            $maskDecoder->setValue(pow(2, count($permissionMap) + 1));
            $maskDecoder->setName('administrate');
            $maskDecoder->setResourceType($resourceType);
            $this->om->persist($maskDecoder);

            //now we edit the edit-rights and open-tracking menu entries
            $editRightsMenu = $this->mm->getMenuFromNameAndResourceType('edit-rights', $resourceType);
            $editRightsMenu->setValue(pow(2, count($permissionMap) + 1));
            $openTrackingMenu = $this->mm->getMenuFromNameAndResourceType('open-tracking', $resourceType);
            $openTrackingMenu->setValue(pow(2, count($permissionMap) + 1));
            $this->om->persist($editRightsMenu);
            $this->om->persist($openTrackingMenu);
        }

        $this->om->flush();
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