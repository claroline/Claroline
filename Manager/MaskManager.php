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
    private static $defaultActions = array('open', 'copy', 'export', 'edit', 'delete');
    private static $defaultMenus= array(
        'export' => array('download' => false),
        'edit' => array('rename' => true, 'edit-properties' => true, 'edit-rights' => true),
        'delete' => array('delete' => false)
    );

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

    /**
     * Returns an array containing the permission for a mask and a resource type.
     *
     * @param integer $mask
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return array
     */
    public function decodeMask($mask, ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(array('resourceType' => $type));
        $perms = array();

        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = ($mask & $decoder->getValue()) ? true: false;
        }

        return $perms;
    }

    /**
     * Encode a mask for an array of permission and a resource type.
     * The array of permissions should be defined that way:
     *
     * array('open' => true, 'edit' => false, ...)
     *
     * @param array $perms
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return integer
     */
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

    /**
     * Returns an array containing the possible permission for a resource type.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return array
     */
    public function getPermissionMap(ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(array('resourceType' => $type));
        $permsMap = array();

        foreach ($decoders as $decoder) {
            $permsMap[] = $decoder->getName();
        }

        return $permsMap;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     * @param string $action
     *
     * @return MaskDecoder
     */
    public function getDecoder(ResourceType $type, $action)
    {
        return $this->maskRepo->findOneBy(array('resourceType' => $type, 'name' => $action));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     * @param integer $value
     *
     * @return MaskDecoder
     */
    public function getByValue(ResourceType $type, $value)
    {
        return $this->maskRepo->findOneBy(array('resourceType' => $type, 'value' => $value));
    }

    /**
     * @param string $name
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return MenuAction
     */
    public function getMenuFromNameAndResourceType($name, ResourceType $type)
    {
        return $this->menuRepo->findOneBy(array('name' => $name, 'resourceType' => $type));
    }

    /**
     * Adds the default action to a resource type.
     * 
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     */
    public function addDefaultPerms(ResourceType $type)
    {
        $createdPerms = array();

        for ($i = 0, $size = count(self::$defaultActions); $i < $size; $i++) {
            $maskDecoder = new MaskDecoder();
            $maskDecoder->setValue(pow(2, $i));
            $maskDecoder->setName(self::$defaultActions[$i]);
            $maskDecoder->setResourceType($type);
            $this->om->persist($maskDecoder);
            $createdPerms[self::$defaultActions[$i]] = $maskDecoder;
        }

        foreach (self::$defaultMenus as $action => $data) {
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