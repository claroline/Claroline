<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Repository\Resource\ResourceMaskDecoderRepository;
use Doctrine\Persistence\ObjectRepository;
use Psr\Log\LoggerAwareInterface;

class MaskManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /**
     * @var array
     *
     * @deprecated this action are now managed like all others
     */
    private static $defaultActions = ['open', 'copy', 'export', 'delete', 'edit', 'administrate'];

    /** @var ObjectManager */
    private $om;

    /** @var ResourceMaskDecoderRepository */
    private $maskRepo;

    /** @var ObjectRepository */
    private $menuRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->maskRepo = $om->getRepository(MaskDecoder::class);
        $this->menuRepo = $om->getRepository(MenuAction::class);
    }

    public function createDecoder($action, ResourceType $resourceType = null)
    {
        /** @var ResourceType[] $resourceTypes */
        $resourceTypes = [];
        if (empty($resourceType)) {
            // we will need to create mask decoder for all resource types
            $resourceTypes = $this->om->getRepository(ResourceType::class)->findAll();
        } else {
            $resourceTypes[] = $resourceType;
        }

        $updated = false;
        foreach ($resourceTypes as $type) {
            // check if the mask already exists
            $decoder = $this->getDecoder($type, $action);
            if (empty($decoder)) {
                $existingDecoders = $this->maskRepo->findBy(['resourceType' => $type]);
                $exp = count($existingDecoders);

                $decoder = new MaskDecoder();
                $decoder->setName($action);
                $decoder->setResourceType($type);
                $decoder->setValue(pow(2, $exp));

                $this->om->persist($decoder);
                $updated = true;
            }
        }

        if ($updated) {
            $this->om->flush();
        }
    }

    /**
     * Returns an array containing the permission for a mask and a resource type.
     *
     * @param int $mask
     *
     * @return array
     */
    public function decodeMask($mask, ResourceType $type)
    {
        /** @var MaskDecoder[] $decoders */
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
     */
    public function encodeMask(array $perms, ResourceType $type): int
    {
        /** @var MaskDecoder[] $decoders */
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
     * Retrieves and removes a mask decoder.
     *
     * @param string $name
     */
    public function removeMask(ResourceType $resourceType, $name)
    {
        $toRemove = $this->getDecoder($resourceType, $name);
        if (!empty($toRemove)) {
            $this->om->remove($toRemove);
        }
    }

    /**
     * Retrieves and renames a mask decoder.
     *
     * @param string $currentName
     * @param string $newName
     */
    public function renameMask(ResourceType $resourceType, $currentName, $newName)
    {
        $toRename = $this->getDecoder($resourceType, $currentName);
        if (!empty($toRename)) {
            $toRename->setName($newName);
            $this->om->persist($toRename);
        }
    }

    /**
     * Returns an array containing the possible permission for a resource type.
     */
    public function getPermissionMap(ResourceType $type): array
    {
        /** @var MaskDecoder[] $decoders */
        $decoders = $this->maskRepo->findBy(['resourceType' => $type]);
        $permsMap = [];

        foreach ($decoders as $decoder) {
            $permsMap[$decoder->getValue()] = $decoder->getName();
        }

        return $permsMap;
    }

    /**
     * @param string $action
     *
     * @return MaskDecoder
     */
    public function getDecoder(ResourceType $type, $action)
    {
        /** @var MaskDecoder $decoder */
        $decoder = $this->maskRepo->findOneBy(['resourceType' => $type, 'name' => $action]);

        return $decoder;
    }

    /**
     * Adds the default action to a resource type.
     *
     * @todo reworks to avoid the use of self::$defaultActions
     */
    public function addDefaultPerms(ResourceType $type)
    {
        /** @var MaskDecoder[] $decoders */
        $decoders = $this->maskRepo->findBy(['resourceType' => $type]);

        $actionNames = [];
        foreach ($decoders as $decoder) {
            $actionNames[] = $decoder->getName();
        }

        // Add only non-existent default actions
        $defaultActions = array_diff(self::$defaultActions, $actionNames);

        foreach ($defaultActions as $i => $action) {
            $maskDecoder = new MaskDecoder();
            $maskDecoder->setValue(pow(2, $i));
            $maskDecoder->setName($action);
            $maskDecoder->setResourceType($type);

            $this->om->persist($maskDecoder);
        }

        $this->om->flush();
    }

    /**
     * Checks if a resource type has any menu actions.
     */
    public function hasMenuAction(ResourceType $type): bool
    {
        $menuActions = $this->menuRepo->findBy(['resourceType' => $type]);

        return count($menuActions) > 0;
    }

    public static function getDefaultActions()
    {
        return self::$defaultActions;
    }

    public function getDefaultResourceActionsMask()
    {
        $actions = [];
        foreach (self::$defaultActions as $action) {
            $actionName = strtoupper($action);
            $actions[$action] = constant(MaskDecoder::class.'::'.$actionName);
        }

        return $actions;
    }
}
