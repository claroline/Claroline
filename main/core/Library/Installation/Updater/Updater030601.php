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

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;

class Updater030601 extends Updater
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

    private function updatePermissionMasksAndMenus()
    {
        $this->log('Updating resource permissions...');
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        foreach ($resourceTypes as $resourceType) {
            $permissionMap = $this->mm->getPermissionMap($resourceType);

            if (!in_array('administrate', $permissionMap)) {
                $mask = $this->mm->getDecoder($resourceType, 'edit');
                $mask->setName('administrate');
                $this->om->persist($mask);
                $maskDecoder = new MaskDecoder();
                $maskDecoder->setValue(pow(2, count($permissionMap) + 1));
                $maskDecoder->setName('edit');
                $maskDecoder->setResourceType($resourceType);
                $this->om->persist($maskDecoder);
            }
        }

        $this->om->flush();
        //remove the "write perm" text.
        $textType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('text');
        $mask = $this->mm->getDecoder($textType, 'write');
        if ($mask) {
            $this->om->remove($mask);
        }

        $fileType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');
        $menuItem = $this->mm->getMenuFromNameAndResourceType('update_file', $fileType);
        $mask = $this->mm->getDecoder($fileType, 'edit');
        $menuItem->setValue($mask->getValue());
        $this->om->persist($menuItem);
        //edit the "change-file" menu

        $this->om->flush();
    }
}
