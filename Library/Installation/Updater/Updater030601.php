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
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;

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

    private function updatePermissionMasksAndMenus()
    {
        $this->log('Updating resource permissions...');
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        foreach ($resourceTypes as $resourceType) {
            try {
                $permissionMap = $this->mm->getPermissionMap($resourceType);

                $mask = $this->mm->getDecoder($resourceType, 'edit');
                $mask->setName('administrate');
                $this->om->persist($mask);
                $maskDecoder = new MaskDecoder();
                $maskDecoder->setValue(pow(2, count($permissionMap) + 1));
                $maskDecoder->setName('edit');
                $maskDecoder->setResourceType($resourceType);

                //add the "edit" perm because it has been... changed !
            } catch (\Exception $e) {
                $this->log("Perms already changed.");
            }
        }

        //remove the "write perm" text.
        $textType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('text');
        $mask = $this->mm->getDecoder($resourceType, 'edit');
        $this->om->remove($mask);

        $fileType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');
        //edit the "change-file" perm
        $menuItem = $this->mm->getMenuFromNameAndResourceType('update-file', $fileType);
        $mask = $this->mm->getDecoder($fileType, 'edit');
        $menuItem->setValue($mask);
        $this->om->persist($mask);
        //edit the "change-file" menu

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