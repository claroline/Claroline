<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\Rights\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
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
    private $ut;

    public function __construct(EntityManager $em, Translator $translator, Utilities $ut)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights');
        $this->translator = $translator;
        $this->validAttributes = array('MOVE', 'COPY', 'DELETE', 'EXPORT', 'CREATE', 'EDIT', 'OPEN');
        $this->ut = $ut;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!in_array($attributes[0], $this->validAttributes)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($object instanceof ResourceCollection) {
            $errors = array();

            if ($attributes[0] == 'CREATE') {
                //there should be one one resource every time
                //(you only create resource one at a time in a single directory
                foreach ($object->getResources() as $resource) {
                    $rightsCreation = $this->repository->getCreationRights($this->ut->getRoles($token), $resource);

                    if (count($rightsCreation) == 0) {
                        $errors[] = $this->translator
                            ->trans('resource_creation_wrong_type',
                                array(
                                    '%path%' => $resource->getPathForDisplay(),
                                    '%type%' => $this->translator->trans(
                                        strtolower($object->getAttribute('type')),
                                        array(),
                                        'resource'
                                    )),
                                'platform'
                            );
                    } else {
                        if (!$this->canCreate($rightsCreation, $object->getAttribute('type'))) {
                            $errors[] = $this->translator
                                ->trans(
                                    'resource_creation_wrong_type',
                                    array(
                                        '%path%' => $resource->getPathForDisplay(),
                                        '%type%' => $this->translator->trans(
                                            strtolower($object->getAttribute('type')),
                                            array(),
                                            'resource')
                                        ),
                                    'platform'
                                );
                        }
                    }
                }
            }

            if ($attributes[0] == 'MOVE') {

                $rightsCreation = $this->repository
                    ->getCreationRights($this->ut->getRoles($token), $object->getAttribute('parent'));

                if (count($rightsCreation) == 0) {
                    $errors[] = $this->translator
                        ->trans(
                            'resource_creation_denied',
                            array('%path%' => $object->getAttribute('parent')->getPathForDisplay()),
                            'platform'
                        );
                } else {
                    foreach ($object->getResources() as $resource) {
                        if (!$this->canCreate($rightsCreation, $resource->getResourceType()->getName())) {
                             $errors[] = $this->translator
                                 ->trans(
                                     'resource_creation_wrong_type',
                                     array(
                                         '%path%' => $object->getAttribute('parent')->getPathForDisplay(),
                                         '%type%' => $this->translator->trans(
                                             strtolower($resource->getResourceType()->getName()),
                                             array(), 'resource')
                                         ),
                                     'platform'
                                 );
                        }

                        $rights = $this->repository->getRights($this->ut->getRoles($token), $resource);

                        if (!$rights['canCopy']) {
                            $errors[] = $this->translator
                                ->trans(
                                    'resource_action_denied_message',
                                    array(
                                        '%path%' => $resource->getPathForDisplay(),
                                        '%action%' => 'COPY'),
                                    'platform'
                                );
                        }

                        if (!$rights['canDelete']) {
                            $errors[] = $this->translator
                                ->trans(
                                    'resource_action_denied_message',
                                    array(
                                        '%path%' => $resource->getPathForDisplay(),
                                        '%action%' => 'DELETE'
                                        ),
                                    'platform'
                                );
                        }
                    }
                }
            }

            if ($attributes[0] == 'COPY') {

                $rightsCreation = $this->repository
                    ->getCreationRights($this->ut->getRoles($token), $object->getAttribute('parent'));

                if (count($rightsCreation) == 0) {
                    $errors[] = $this->translator
                        ->trans(
                            'resource_creation_denied',
                            array('%path%' => $object->getAttribute('parent')->getPathForDisplay()),
                            'platform'
                        );
                } else {
                    foreach ($object->getResources() as $resource) {
                        if (!$this->canCreate($rightsCreation, $resource->getResourceType()->getName())) {
                            $errors[] = $this->translator
                                ->trans('resource_creation_wrong_type',
                                    array(
                                        '%path%' => $object->getAttribute('parent')->getPathForDisplay(),
                                        '%type%' => $this->translator->trans(
                                            strtolower($resource->getResourceType()->getName()),
                                            array(),
                                            'resource')
                                        ),
                                    'platform'
                                );
                        }
                    }
                }
            }

            $call = "can" . ucfirst(strtolower($attributes[0]));
            $action = strtoupper($attributes[0]);
            $rr = new ResourceRights;

            if (method_exists($rr, $call)) {
                foreach ($object->getResources() as $resource) {
                    $rights = $this->repository->getRights($this->ut->getRoles($token), $resource);

                    if ($rights == null) {
                        $errors[] = $this->translator
                            ->trans(
                                'resource_action_denied_message',
                                array(
                                    '%path%' => $resource->getPathForDisplay(),
                                    '%action%' => $action
                                    ),
                                'platform'
                            );
                    } else {
                        if (!$this->canDo($resource, $token, $action)) {
                            $errors[] = $this->translator
                                ->trans(
                                    'resource_action_denied_message',
                                    array(
                                        '%path%' => $resource->getPathForDisplay(),
                                        '%action%' => $action
                                        ),
                                    'platform'
                                );
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

    /**
     * Checks if the resourceType name $resourceType is in the
     * $rightsCreation array.
     *
     * @param array $rightsCreation
     * @param string $resourceType
     *
     * @return boolean
     */
    private function canCreate(array $rightsCreation, $resourceType)
    {
        foreach ($rightsCreation as $item) {
            if ($item['name'] == $resourceType) {
                return true;
            }
        }

        return false;
    }


    /**
     * Checks if the current token has the right to do the action $action.
     *
     * @param AbstractResource $resource
     * @param TokenInterface $token
     * @param string $action
     *
     * @return boolean
     */
    private function canDo(AbstractResource $resource, TokenInterface $token, $action)
    {
        $rights = $this->em
            ->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->getRights($this->ut->getRoles($token), $resource);
        $permission = 'can'.ucfirst(strtolower($action));

        return $rights[$permission];
    }
}