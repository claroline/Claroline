<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\Translator;
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
    private $translator;
    private $validAttributes;

    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights');
        $this->translator = $translator;
        $this->validAttributes = array('MOVE', 'COPY', 'DELETE', 'EXPORT', 'CREATE', 'EDIT', 'OPEN');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!in_array($attributes[0], $this->validAttributes)){
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($object instanceof ResourceCollection){
            $errors = array();

            if ($attributes[0] == 'CREATE') {
                //there should be one one resource every time (you only create resource one at a time in a single directory
                foreach ($object->getResources() as $resource) {
                    $rights = $this->repository->getRights($token->getUser(), $resource);

                    if ($rights == null) {
                        $errors[] = $this->translator->trans('resource_creation_wrong_type', array('%path%' => $resource->getPathForDisplay(), '%type%' => $this->translator->trans(strtolower($object->getAttribute('type')), array(), 'resource')), 'platform');
                    } else {
                        if (!$this->canCreate($rights, $object->getAttribute('type'))) {
                            $errors[] = $this->translator->trans('resource_creation_wrong_type', array('%path%' => $resource->getPathForDisplay(), '%type%' => $this->translator->trans(strtolower($object->getAttribute('type')), array(), 'resource')), 'platform');
                        }
                    }
                }
            }

            if ($attributes[0] == 'MOVE'){

                $parentRights = $this->repository->getRights($token->getUser(), $object->getAttribute('parent'));

                if ($parentRights == null) {
                    $errors[] = $this->translator->trans('resource_creation_denied', array('%path%' => $object->getAttribute('parent')->getPathForDisplay()), 'platform');
                } else {
                    foreach($object->getResources() as $resource){
                        if (!$this->canCreate($parentRights, $resource->getResourceType()->getName())) {
                             $errors[] = $this->translator->trans('resource_creation_wrong_type', array('%path%' => $object->getAttribute('parent')->getPathForDisplay(), '%type%' => $this->translator->trans(strtolower($resource->getResourceType()->getName()), array(), 'resource')), 'platform');
                        }

                        $rights = $this->repository->getRights($token->getUser(), $resource);

                        if(!$rights->canCopy()){
                            $errors[] = $this->translator->trans('resource_action_denied_message', array('%path%' => $resource->getPathForDisplay(), '%action%' => 'COPY'), 'platform');
                        }

                        if(!$rights->canDelete()){
                            $errors[] = $this->translator->trans('resource_action_denied_message', array('%path%' => $resource->getPathForDisplay(), '%action%' => 'DELETE'), 'platform');
                        }
                    }
                }
            }

            if ($attributes[0] == 'COPY') {

                $parentRights = $this->repository->getRights($token->getUser(), $object->getAttribute('parent'));

                if ($parentRights == null) {
                    $errors[] = $this->translator->trans('resource_creation_denied', array('%path%' => $object->getAttribute('parent')->getPathForDisplay()), 'platform');
                } else {
                    foreach ($object->getResources() as $resource) {
                        if (!$this->canCreate($parentRights, $resource->getResourceType()->getName())) {
                            $errors[] = $this->translator->trans('resource_creation_wrong_type', array('%path%' => $object->getAttribute('parent')->getPathForDisplay(), '%type%' => $this->translator->trans(strtolower($resource->getResourceType()->getName()), array(), 'resource')), 'platform');
                        }
                    }
                }
            }

            $call = "can" . ucfirst(strtolower($attributes[0]));
            $action = strtoupper($attributes[0]);
            $rr = new ResourceRights;

            if (method_exists($rr, $call)) {
                foreach ($object->getResources() as $resource) {
                    $rights = $this->repository->getRights($token->getUser(), $resource);

                    if($rights == null){
                        $errors[] = $this->translator->trans('resource_action_denied_message', array('%path%' => $resource->getPathForDisplay(), '%action%' => $action), 'platform');
                    } else {
                        if (!$rights->$call()){
                            $errors[] = $this->translator->trans('resource_action_denied_message', array('%path%' => $resource->getPathForDisplay(), '%action%' => $action), 'platform');
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
        $resourceTypes = $rights->getResourceTypes();

        foreach ($resourceTypes as $item) {
            if ($item->getName() == $resourceType) {
                return true;
            }
        }

        return false;
    }
}