<?php

namespace Claroline\CoreBundle\Manager;

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

    /**
     * Constructor.
     *
     * @DI\InjectParams({"om" = @DI\Inject("claroline.persistence.object_manager")})
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->maskRepo = $om->getRepository('ClarolineCoreBundle:Resource\MaskDecoder');
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
}