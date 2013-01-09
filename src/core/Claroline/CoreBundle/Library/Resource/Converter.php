<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;

class Converter
{
    /* @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function toArray(AbstractResource $resource, User $user)
    {
        $resourceArray = array();
        $resourceArray['id'] = $resource->getId();
        $resourceArray['name'] = $resource->getName();
        ($resource->getParent() != null) ? $resourceArray['parent_id'] = $resource->getParent()->getId() : $resourceArray['parent_id'] = null;
        $resourceArray['creator_username'] = $resource->getCreator()->getUsername();
        $resourceArray['type'] = $resource->getResourceType()->getName();
        $resourceArray['is_browsable'] = $resource->getResourceType()->getBrowsable();
        $resourceArray['large_icon'] = $resource->getIcon()->getRelativeUrl();
        $resourceArray['path_for_display'] = $resource->getPathForDisplay();

        $isAdmin = false;
        foreach($user->getRoles() as $role){
           if($role == 'ROLE_ADMIN'){
               $isAdmin = true;
           }
        }

        if($isAdmin){
            $resourceArray['can_export'] = true;
            $resourceArray['can_edit'] = true;
            $resourceArray['can_delete'] = true;
        } else {
            $rights = $this->em->getRepository('Claroline\CoreBundle\Entity\Workspace\ResourceRights')->getRights($user, $resource);
            $resourceArray['can_export'] = $rights->canExport();
            $resourceArray['can_edit'] = $rights->canEdit();
            $resourceArray['can_delete'] = $rights->canDelete();
        }

        return $resourceArray;
    }

    public function toJson(AbstractResource $resource, User $user)
    {
        $phpArray[0] = $this->toArray($resource, $user);
        $json = json_encode($phpArray);

        return $json;
    }
}