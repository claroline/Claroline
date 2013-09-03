<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.mask_manager")
 */
class MaskManager
{
    private $om;
    private $maskRepo;
    private $menuRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({"om" = @DI\Inject("claroline.persistence.object_manager")})
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->maskRepo = $om->getRepository('ClarolineCoreBundle:Resource\MaskDecoder');
        $this->menuRepo = $om->getRepository('ClarolineCoreBundle:Resource\MenuAction');
    }

    public function decodeMask($mask, ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(array('resourceType' => $type));
        $perms = array();

        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = ($mask & $decoder->getValue()) ? true: false;
        }

        return $perms;
    }

    public function encodeMask($perms, ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(array('resourceType' => $type));
        $mask = 0;

        foreach ($decoders as $decoder) {
            if (isset($perms[$decoder->getName()])) {
                $mask += $perms[$decoder->getName()] ? $decoder->getValue(): 0;
            }
        }

        return $mask;
    }

    public function getPermissionMap(ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(array('resourceType' => $type));
        $permsMap = array();

        foreach ($decoders as $decoder) {
            $permsMap[] = $decoder->getName();
        }

        return $permsMap;
    }

    public function getDecoder(ResourceType $type, $action)
    {
        return $this->maskRepo->findOneBy(array('resourceType' => $type, 'name' => $action));
    }

    public function getByValue(ResourceType $type, $value)
    {
        return $this->maskRepo->findOneBy(array('resourceType' => $type, 'value' => $value));
    }

    public function getMenuFromNameAndResourceType($name, ResourceType $type)
    {
        return $this->menuRepo->findOneBy(array('name' => $name, 'resourceType' => $type));
    }

    public function addDefaultPerms(ResourceType $type)
    {
        $defaultPerms = array('open', 'copy', 'export', 'edit', 'delete');
        $createdPerms = array();

        $menuMap = array(
            'export' => array('download' => false),
            'edit' => array('rename' => true, 'edit-properties' => true, 'edit-rights' => true),
            'delete' => array('delete' => false)
        );

        for ($i = 0, $size = count($defaultPerms); $i < $size; $i++) {
            $maskDecoder = new MaskDecoder();
            $maskDecoder->setValue(pow(2, $i));
            $maskDecoder->setName($defaultPerms[$i]);
            $maskDecoder->setResourceType($type);
            $this->om->persist($maskDecoder);
            $createdPerms[$defaultPerms[$i]] = $maskDecoder;
        }

        foreach ($menuMap as $action => $data) {
            foreach ($data as $name => $isForm) {
                $menu = new MenuAction();
                $menu->setName($name);
                $menu->setAsync(true);
                $menu->setIsCustom(false);
                $menu->setValue($createdPerms[$action]->getValue());
                $menu->setResourceType($type);
                $menu->setIsForm($isForm);
                $this->om->persist($menu);
            }
        }

        $this->om->flush();
    }
}