<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    private static $defaultActions = ['open', 'copy', 'export', 'delete', 'edit', 'administrate'];

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

    public function restoreIntegrity()
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * Returns an array containing the permission for a mask and a resource type.
     *
     * @param int                                                $mask
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return array
     */
    public function decodeMask($mask, ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(['resourceType' => $type]);
        $perms = [];

        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = ($mask & $decoder->getValue()) ? true : false;
        }

        return $perms;
    }

    /**
     * Encode a mask for an array of permission and a resource type.
     * The array of permissions should be defined that way:.
     *
     * array('open' => true, 'edit' => false, ...)
     *
     * @param array                                              $perms
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return int
     */
    public function encodeMask($perms, ResourceType $type)
    {
        $decoders = $this->maskRepo->findBy(['resourceType' => $type]);
        $mask = 0;

        foreach ($decoders as $decoder) {
            if (isset($perms[$decoder->getName()])) {
                $mask += $perms[$decoder->getName()] ? $decoder->getValue() : 0;
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
        $decoders = $this->maskRepo->findBy(['resourceType' => $type]);
        $permsMap = [];

        foreach ($decoders as $decoder) {
            $permsMap[$decoder->getValue()] = $decoder->getName();
        }

        return $permsMap;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     * @param string                                             $action
     *
     * @return MaskDecoder
     */
    public function getDecoder(ResourceType $type, $action)
    {
        return $this->maskRepo->findOneBy(['resourceType' => $type, 'name' => $action]);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     * @param int                                                $value
     *
     * @return MaskDecoder
     */
    public function getByValue(ResourceType $type, $value)
    {
        return $this->maskRepo->findOneBy(['resourceType' => $type, 'value' => $value]);
    }

    /**
     * @param string                                             $name
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return MenuAction
     */
    public function getMenuFromNameAndResourceType($name, ResourceType $type)
    {
        if ($this->menuRepo->findOneBy(['name' => $name, 'resourceType' => $type])) {
            return $this->menuRepo->findOneBy(['name' => $name, 'resourceType' => $type]);
        }

        return $this->menuRepo->findOneBy(['name' => $name]);
    }

    /**
     * Adds the default action to a resource type.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     */
    public function addDefaultPerms(ResourceType $type)
    {
        $createdPerms = [];

        for ($i = 0, $size = count(self::$defaultActions); $i < $size; ++$i) {
            $maskDecoder = new MaskDecoder();
            $maskDecoder->setValue(pow(2, $i));
            $maskDecoder->setName(self::$defaultActions[$i]);
            $maskDecoder->setResourceType($type);
            $this->om->persist($maskDecoder);
            $createdPerms[self::$defaultActions[$i]] = $maskDecoder;
        }

        $this->om->flush();
    }

    /**
     * Checks if a resource type has any menu actions.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     */
    public function hasMenuAction(ResourceType $type)
    {
        $menuActions = $this->menuRepo->findBy(
            ['resourceType' => $type]
        );

        return count($menuActions) > 0;
    }

    public function getDefaultActions()
    {
        return self::$defaultActions;
    }
}
