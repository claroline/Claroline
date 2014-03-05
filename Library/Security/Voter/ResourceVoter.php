<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
                $targetWorkspace = $object->getResources()[0]->getWorkspace();

                foreach ($object->getResources() as $resource) {
                    $errors = array_merge(
                        $errors,
                        $this->checkCreation($object->getAttribute('type'), $resource, $token, $targetWorkspace)
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

    /**
     * @todo remove array typing from $resources
     *
     * @param $action
     * @param array $nodes
     * @param TokenInterface $token
     * @return array
     * @throws \Exception
     */
    public function checkAction($action, array $nodes, TokenInterface $token)
    {
        $haveSameWorkspace = true;
        $ws = $nodes[0]->getWorkspace();

        foreach ($nodes as $node) {
            if ($node->getWorkspace() !== $ws) {
                $haveSameWorkspace = false;
                break;
            }
        }

        //the workspace manager he can do w/e he wants
        if ($haveSameWorkspace && $this->isWorkspaceManager($ws, $token)) {
            return array();
        }

        //the resource creator can do w/e he wants
        $timesCreator = 0;

        foreach ($nodes as $node) {
            if ($node->getCreator() === $token->getUser()) {
                $timesCreator++;
            }
        }

        if ($timesCreator == count($nodes)) {
            return array();
        }

        //check if the action is possible on the node
        $errors = array();
        $action = strtolower($action);

        foreach ($nodes as $node) {
            $mask = $this->repository->findMaximumRights($this->ut->getRoles($token), $node);
            $type = $node->getResourceType();
            $decoder = $this->maskManager->getDecoder($type, $action);

            if (!$decoder) {
                return array('The permission ' . $action . ' does not exists for the type ' . $type->getName());
            }

            $grant = $decoder ? $mask & $decoder->getValue(): 0;

            if ($decoder && $grant === 0) {
                $errors[] = $this->getRoleActionDeniedMessage($action, $node->getPathForDisplay());
            }
        }

        return $errors;
    }

    /**
     * Checks if the a resource whole type is $type
     * can be created in the directory $resource by the $token
     *
     * @param array $types
     * @param ResourceNode $node
     * @param TokenInterface $token
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function checkCreation(
        array $types,
        ResourceNode $node,
        TokenInterface $token,
        AbstractWorkspace $workspace
    )
    {
        $errors = array();

        if ($this->isWorkspaceManager($workspace, $token)) {
            return $errors;
        }

        $rightsCreation = $this->repository->findCreationRights($this->ut->getRoles($token), $node);

        if (count($rightsCreation) == 0) {
            $errors[] = $this->translator
                ->trans(
                    'resource_creation_wrong_type',
                    array(
                        '%path%' => $node->getPathForDisplay(),
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
                            '%path%' => $node->getPathForDisplay(),
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
    public function checkMove(ResourceNode $parent, $resources, TokenInterface $token)
    {
        //do the resources share the same workspace ?
        //It doesn't cover every cases like moving a resource from a workspace to an other where the user
        //is manager of both but it is a start.
        //role manager verification
        $haveSameWorkspace = true;
        $ws = $parent->getWorkspace();

        foreach ($resources as $resource) {
            if ($resource->getWorkspace() !== $ws) {
                $haveSameWorkspace = false;
                break;
            }
        }

        if ($haveSameWorkspace && $this->isWorkspaceManager($ws, $token)) {
            return array();
        }

        //end of the role manager verification

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
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     * @param array|\Claroline\CoreBundle\Library\Security\Voter\type $resources
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function checkCopy(ResourceNode $parent, array $resources, TokenInterface $token)
    {
        //do the resources share the same workspace ?
        //It doesn't cover every cases like copying a resource from a workspace to an other where the user
        //is manager of both but it is a start.
        //role manager verification
        $haveSameWorkspace = true;
        $ws = $parent->getWorkspace();

        foreach ($resources as $resource) {
            if ($resource->getWorkspace() !== $ws) {
                $haveSameWorkspace = false;
            }
        }

        if ($haveSameWorkspace && $this->isWorkspaceManager($ws, $token)) {
            return array();
        }

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

    public function isWorkspaceManager(AbstractWorkspace $workspace, TokenInterface $token)
    {
        $managerRoleName = 'ROLE_WS_MANAGER_' . $workspace->getGuid();

        return in_array($managerRoleName, $this->ut->getRoles($token)) ? true: false;
    }
}
