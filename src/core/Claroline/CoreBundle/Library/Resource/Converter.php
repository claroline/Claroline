<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Security\RightsManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Converter
{
    /* @var EntityManager */
    private $em;
    private $rm;

    public function __construct(EntityManager $em, RightsManager $rm)
    {
        $this->em = $em;
        $this->rm = $rm;
    }

    public function toArray(AbstractResource $resource, TokenInterface $token)
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

        $roles = $this->rm->getRoles($token);
        foreach($roles as $role){
            if($role === 'ROLE_ADMIN'){
                $isAdmin = true;
            }
        }

        if($isAdmin){
            $resourceArray['can_export'] = true;
            $resourceArray['can_edit'] = true;
            $resourceArray['can_delete'] = true;
        } else {
            $rights = $this->em->getRepository('Claroline\CoreBundle\Entity\Rights\ResourceRights')->getRights($roles, $resource);
            $resourceArray['can_export'] = $rights['canExport'];
            $resourceArray['can_edit'] = $rights['canEdit'];
            $resourceArray['can_delete'] = $rights['canDelete'];
        }

        return $resourceArray;
    }

    public function toJson(AbstractResource $resource, TokenInterface $token)
    {
        $phpArray[0] = $this->toArray($resource, $token);
        $json = json_encode($phpArray);

        return $json;
    }
}