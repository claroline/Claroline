<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class Converter
{
    /* @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function resourceToArray(AbstractResource $resource)
    {
        $resourceArray = array();
        $resourceArray['id'] = $resource->getId();
        $resourceArray['name'] = $resource->getName();
        $resourceArray['created'] = $resource->getCreationDate()->format('d-m-Y H:i:s');
        $resourceArray['updated'] = $resource->getModificationDate()->format('d-m-Y H:i:s');;
        $resourceArray['path'] = $resource->getPath();
        $resourceArray['lvl'] = $resource->getLvl();
        ($resource->getParent() != null) ? $resourceArray['parent_id'] = $resource->getParent()->getId() : $resourceArray['parent_id'] = null;
        $resourceArray['workspace_id'] = $resource->getWorkspace()->getId();
        $resourceArray['creator_id'] = $resource->getCreator()->getId();
        $resourceArray['creator_username'] = $resource->getCreator()->getUsername();
        $resourceArray['resource_type_id'] = $resource->getResourceType()->getId();
        $resourceArray['type'] = $resource->getResourceType()->getName();
        $resourceArray['is_browsable'] = $resource->getResourceType()->getBrowsable();
        $resourceArray['small_icon'] = $resource->getIcon()->getSmallIcon();
        $resourceArray['large_icon'] = $resource->getIcon()->getLargeIcon();
        $resourceArray['path'] = $resource->getPath();
        $resourceArray['path_for_display'] = $resource->getPathForDisplay();
        $array = array();
        $array[0] = $resourceArray;

        return $array;
    }

    public function ResourceToJson(AbstractResource $resource)
    {
        $phpArray = $this->resourceToArray($resource);
        $json = json_encode($phpArray);

        return $json;
    }
}