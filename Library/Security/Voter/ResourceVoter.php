<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for AbstractResource instances.
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class ResourceVoter implements VoterInterface
{
    private $em;
    private $repository;
    private $translator;
    private $specialActions ;
    private $ut;
    private $maskManager;

    /**
     * @DI\InjectParams({
     *     "em"           = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator"   = @DI\Inject("translator"),
     *     "ut"           = @DI\Inject("claroline.security.utilities"),
     *     "maskManager"  = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct(EntityManager $em, Translator $translator, Utilities $ut, MaskManager $maskManager)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->translator = $translator;
        $this->specialActions = array('move', 'create', 'copy');
        $this->ut = $ut;
        $this->maskManager = $maskManager;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
       $object = $object instanceof AbstractResource ? $object->getResourceNode(): $object;

        if ($object instanceof ResourceCollection) {
            $errors = array();

            if (strtolower($attributes[0]) == 'create') {
                //there should be one one resource every time
                //(you only create resource one at a time in a single directory
                foreach ($object->getResources() as $resource) {
                    $errors = array_merge(
                        $errors,
                        $this->checkCreation($object->getAttribute('type'), $resource, $token)
                    );
                }
            } elseif (strtolower($attributes[0]) == 'move') {
                $errors = array_merge(
                    $errors,
                    $this->checkMove($object->getAttribute('parent'), $object->getResources(), $token)
                );
            } elseif (strtolower($attributes[0]) == 'copy') {
                $errors = array_merge(
                    $errors,
                    $this->checkCopy($object->getAttribute('parent'), $object->getResources(), $token)
                );
            } else {
                $errors = array_merge(
                    $errors,
                    $this->checkAction(strtolower($attributes[0]), $object->getResources(), $token)
                );
            }

            if (count($errors) === 0) {
                return VoterInterface::ACCESS_GRANTED;
            }

            $object->setErrors($errors);

            return VoterInterface::ACCESS_DENIED;

        } elseif ($object instanceof ResourceNode) {

            if (in_array($attributes[0], $this->specialActions)) {
                throw new \Exception('A ResourceCollection class must be used for this action.');
            }

            $errors = $this->checkAction($attributes[0], array($object), $token);

            return count($errors) === 0 ? VoterInterface::ACCESS_GRANTED: VoterInterface::ACCESS_DENIED;

        }

        return VoterInterface::ACCESS_ABSTAIN;
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
     * @param array  $rightsCreation
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

    private function checkAction($action, array $resources, TokenInterface $token)
    {
        $isCreator = false;

        foreach ($resources as $resource) {
            if ($resource->getCreator() === $token->getUser()) {
                $isCreator = true;
            }
        }

        if ($isCreator) {
            return array();
        }

        $errors = array();
        $action = strtolower($action);

        foreach ($resources as $resource) {
            $mask = $this->repository->findMaximumRights($this->ut->getRoles($token), $resource);
            $type = $resource->getResourceType();
            $decoder = $this->maskManager->getDecoder($type, $action);

            if (!$decoder) {
                throw new \Exception('The permission ' . $action . ' does not exists for the type ' . $type->getName());
            }

            $grant = $decoder ? $mask & $decoder->getValue(): 0;

            if ($decoder && $grant === 0) {
                $errors[] = $this->getRoleActionDeniedMessage($action, $resource->getPathForDisplay());
            }
        }

        return $errors;
    }

    /**
     * Checks if the a resource whole type is $type
     * can be created in the directory $resource by the $token
     *
     * @param string         $type
     * @param ResourceNode   $resource
     * @param TokenInterface $token
     *
     * @return array
     */
    private function checkCreation($types, ResourceNode $resource, TokenInterface $token)
    {
        $rightsCreation = $this->repository->findCreationRights($this->ut->getRoles($token), $resource);
        $errors = array();

        if (count($rightsCreation) == 0) {
            $errors[] = $this->translator
                ->trans(
                    'resource_creation_wrong_type',
                    array(
                        '%path%' => $resource->getPathForDisplay(),
                        '%type%' => $this->translator->trans(
                            strtolower($types),
                            array(),
                            'resource'
                        )
                    ),
                    'platform'
                );
        } else {
            if (!$this->canCreate($rightsCreation, $types)) {
                $errors[] = $this->translator
                    ->trans(
                        'resource_creation_wrong_type',
                        array(
                            '%path%' => $resource->getPathForDisplay(),
                            '%type%' => $this->translator->trans(
                                strtolower($types), array(), 'resource'
                            )
                        ),
                        'platform'
                    );
            }
        }

        return $errors;
    }

    /**
     * Checks if the array of resources can be moved to the resource $parent
     * by the $token.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode                   $parent
     * @param array                                                                $resources
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    private function checkMove(ResourceNode $parent, $resources, TokenInterface $token)
    {
        $errors = array();
        $rightsCreation = $this->repository
            ->findCreationRights($this->ut->getRoles($token), $parent);

        if (count($rightsCreation) == 0) {
            $errors[] = $this->translator
                ->trans(
                    'resource_creation_denied',
                    array('%path%' => $parent->getPathForDisplay()),
                    'platform'
                );
        } else {
            foreach ($resources as $resource) {
                if (!$this->canCreate($rightsCreation, $resource->getResourceType()->getName())) {
                     $errors[] = $this->translator
                         ->trans(
                             'resource_creation_wrong_type',
                             array(
                                 '%path%' => $parent->getPathForDisplay(),
                                 '%type%' => $this->translator->trans(
                                     strtolower($resource->getResourceType()->getName()),
                                     array(),
                                     'resource'
                                 )
                             ),
                             'platform'
                         );
                }

                $mask = $this->repository->findMaximumRights($this->ut->getRoles($token), $resource);
                $grantCopy = $mask & MaskDecoder::COPY;
                if ($grantCopy === 0) {
                    $errors[] = $this->getRoleActionDeniedMessage('copy', $resource->getPathForDisplay());
                }

                $grantDelete = $mask & MaskDecoder::DELETE;
                if ($grantDelete === 0) {
                    $errors[] = $this->getRoleActionDeniedMessage('delete', $resource->getPathForDisplay());
                }
            }
        }

        return $errors;
    }

    /**
     * Checks if the array of resources can be copied to the resource $parent
     * by the $token.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode                   $parent
     * @param type                                                                 $resources
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function checkCopy(ResourceNode $parent, array $resources, TokenInterface $token)
    {
        $errors = array();
        $rightsCreation = $this->repository
            ->findCreationRights($this->ut->getRoles($token), $parent);

        if (count($rightsCreation) == 0) {
            $errors[] = $this->translator
                ->trans(
                    'resource_creation_denied',
                    array('%path%' => $parent->getPathForDisplay()),
                    'platform'
                );
        } else {
            foreach ($resources as $resource) {
                if (!$this->canCreate($rightsCreation, $resource->getResourceType()->getName())) {
                    $errors[] = $this->translator
                        ->trans(
                            'resource_creation_wrong_type',
                            array(
                                '%path%' => $parent->getPathForDisplay(),
                                '%type%' => $this->translator->trans(
                                    strtolower($resource->getResourceType()->getName()),
                                    array(),
                                    'resource'
                                )
                            ),
                            'platform'
                        );
                }
            }
        }

        return $errors;
    }

    public function getRoleActionDeniedMessage($action, $path)
    {
        return $this->translator
            ->trans(
                'resource_action_denied_message',
                array(
                    '%path%' => $path,
                    '%action%' => $action
                    ),
                'platform'
            );
    }
}
