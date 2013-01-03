<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\Workspace\ResourceRights;
use Doctrine\ORM\EntityManager;

/**
 * This voter is involved in access decisions for AbstractResource instances.
 */
class ResourceVoter implements VoterInterface
{
    private $em;
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {

        if ($object instanceof ResourceCollection){
            $errors = array();

            if ($attributes[0] == 'CREATE') {
                foreach ($object->getResources() as $resource) {
                    $rights = $this->repository->getRights($token->getUser(), $resource);

                    if ($rights == null) {
                        $errors[] = "[CREATE]: Access denied for resource {$resource->getPathForDisplay()}: no role found.";
                    } else {
                        if (!$this->canCreate($rights, $attributes[1])) {
                            $errors[] = "[CREATE] Can not create {$attributes[1]} in {$resource->getPathForDisplay()}: Access denied.";
                        }
                    }
                }
            }

            if ($attributes[0] == 'MOVE'){

                foreach($object->getResources() as $resource){

                    $rights = $this->repository->getRights($token->getUser(), $resource);
                    $parentRights = $this->repository->getRights($token->getUser(), $attributes[1]);

                    if ($parentRights == null) {
                        $errors[] = "[CREATE]: Access denied for resource {$resource->getPathForDisplay()}: no role found.";
                    } else {
                        if (!$this->canCreate($parentRights, $resource->getResourceType()->getName())) {
                            $errors[] = "[CREATE] Can not create {$resource->getResourceType()->getName()} in {$attributes[1]->getPathForDisplay()}: Access denied.";
                        }
                    }

                    if(!$rights->canCopy()){
                        $errors[] = "[MOVE]: Can not copy {$resource->getPathForDisplay()}: Access denied.";
                    }

                    if(!$rights->canDelete()){
                        $errors[] = "[MOVE]: Can not delete {$resource->getPathForDisplay()}: Access denied.";
                    }
                }
            }

            if ($attributes[0] == 'COPY') {

                foreach ($object->getResources() as $resource) {

                    $rights = $this->repository->getRights($token->getUser(), $resource);
                    
                    $parentRights = $this->repository->getRights($token->getUser(), $attributes[1]);

                    if ($parentRights == null) {
                        $errors[] = "[CREATE]: Access denied for resource {$resource->getPathForDisplay()}: no role found.";
                    } else {
                        if (!$this->canCreate($parentRights, $resource->getResourceType()->getName())) {
                            $errors[] = "[CREATE] Can not create {$resource->getResourceType()->getName()} in {$attributes[1]->getPathForDisplay()}: Access denied.";
                        }
                    }
/*
                    if(!$rights->canCopy()){
                        $errors[] = "[COPY]: Can not copy {$resource->getPathForDisplay()}: Access denied.";
                    }*/
                }
            }

            $call = "can" . ucfirst(strtolower($attributes[0]));
            $action = strtoupper($attributes[0]);
            $rr = new ResourceRights;
            if (method_exists($rr, $call)) {
                foreach ($object->getResources() as $resource) {
                    $rights = $this->repository->getRights($token->getUser(), $resource);

                    if($rights == null){

                        $errors[] = "[{$action}]: Access denied for resource {$resource->getPathForDisplay()}: no role found.";
                    } else {
                        if (!$rights->$call()){
                            $errors[] = "[{$action}]: Access denied for resource {$resource->getPathForDisplay()}: user {$token->getUser()->getUserName()} doesn't have the permission {$attributes[0]}";
                        }
                    }
                }
            }

            if (count($errors) == 0) {
                return VoterInterface::ACCESS_GRANTED;
            } else {
                $object->setErrors($errors);
                return VoterInterface::ACCESS_DENIED;
            }
        } else {
             return VoterInterface::ACCESS_ABSTAIN;
        }
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    private function canCreate($rights, $resourceType)
    {
        if ($rights->canCreate()) {
            if (count($rights->getResourceTypes()) == 0) {
                return true;
            } else {

                $resourceTypes = $rights->getResourceTypes();
                foreach ($resourceTypes as $item) {
                    if ($item->getName() == $resourceType) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}