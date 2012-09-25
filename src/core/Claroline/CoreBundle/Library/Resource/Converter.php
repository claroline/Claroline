<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;

class Converter
{
    /* @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function instanceToArray(ResourceInstance $instance)
    {
        $instanceArray = array();
        $instanceArray['id'] = $instance->getId();
        $instanceArray['name'] = $instance->getName();
        $instanceArray['created'] = $instance->getCreationDate()->format('d-m-Y H:i:s');
        $instanceArray['updated'] = $instance->getModificationDate()->format('d-m-Y H:i:s');;
        $instanceArray['path'] = $instance->getPath();
        $instanceArray['lvl'] = $instance->getLvl();
        ($instance->getParent() != null) ? $instanceArray['parent_id'] = $instance->getParent()->getId() : $instanceArray['parent_id'] = null;
        $instanceArray['workspace_id'] = $instance->getWorkspace()->getId();
        $instanceArray['resource_id'] = $instance->getResource()->getId();
        $instanceArray['instance_creator_id'] = $instance->getCreator()->getId();
        $instanceArray['instance_creator_username'] = $instance->getCreator()->getUsername();
        $instanceArray['resource_creator_id'] = $instance->getResource()->getCreator()->getId();
        $instanceArray['resource_creator_username'] = $instance->getResource()->getCreator()->getUsername();
        $instanceArray['resource_type_id'] = $instance->getResource()->getResourceType()->getId();
        $instanceArray['type'] = $instance->getResource()->getResourceType()->getType();
        $instanceArray['is_navigable'] = $instance->getResourceType()->getNavigable();
        $instanceArray['small_icon'] = $instance->getResource()->getIcon()->getSmallIcon();
        $instanceArray['large_icon'] = $instance->getResource()->getIcon()->getLargeIcon();
        $instanceArray['path'] = $instance->getPath();
        $instanceArray['path_for_display'] = $instance->getPathForDisplay();
        $array = array();
        $array[0] = $instanceArray;

        return $array;
    }

    public function instanceToJson(ResourceInstance $instance)
    {
        $phpArray = $this->instanceToArray($instance);
        $json = json_encode($phpArray);

        return $json;
    }
}