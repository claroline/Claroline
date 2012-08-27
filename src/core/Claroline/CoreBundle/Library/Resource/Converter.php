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
        $instanceArray['lft'] = $instance->getLft();
        $instanceArray['lvl'] = $instance->getLvl();
        $instanceArray['rgt'] = $instance->getRgt();
        $instanceArray['root'] = $instance->getRoot();
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
        // null or use doctrine to retrieve the path
        $repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $nodes = $repo->getPath($instance);
        $path = '';
        foreach ($nodes as $node) {
            $path.="{$node->getName()} >";
        }
        $instanceArray['path'] = $path;
        $array = array();
        $array[0] = $instanceArray;

        return $array;
    }

    public function arrayToJson($results)
    {
        $json = "[";
        $i = 0;
        foreach ($results as $item){
            $stringitem ='';
            if($i != 0){
                $stringitem.=",";
            } else {
                $i++;
            }
            $stringitem.= '{';
            $stringitem.= ' "title": "'.$item['name'].'", ';
            $stringitem.= ' "key": "'.$item['id'].'", ';
            $stringitem.= ' "instanceId": "'.$item['id'].'", ';
            $stringitem.= ' "resourceId": "'.$item['resource_id'].'", ';
            $stringitem.= ' "type": "'.$item['type'].'", ';
            $stringitem.= ' "typeId": "'.$item['resource_type_id'].'", ';
            $stringitem.= ' "workspaceId": "'.$item['workspace_id'].'", ';
            $stringitem.= ' "dateInstanceCreation": "'.$item['created'].'" ';
            $stringitem.= ' , "small_icon": "'.$item['small_icon'].'" ';
            $stringitem.= ' , "large_icon":"'.$item['large_icon'].'" ';

            if (array_key_exists('path', $item)) {
                $stringitem.= ' , "path":"'.$item['path'].'" ';
            }
            /*
             * dynatree needs these fields
            if ($item['is_navigable'] != 0) {
                $stringitem.=', "isFolder": true ';
                $stringitem.=', "isLazy": true ';
            }*/
            $stringitem.='}';
            $json.=$stringitem;
        }

        $json.="]";

        return $json;
    }

    public function instanceToJson(ResourceInstance $instance)
    {
        $phpArray = $this->instanceToArray($instance);
        $json = $this->arrayToJson($phpArray);

        return $json;
    }

}