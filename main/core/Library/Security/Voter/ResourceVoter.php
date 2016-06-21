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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for AbstractResource instances.
 *
 * Please note that the 'ADMINISTRATE' perm does a lot of things and it's sadly not always
 * decided here (see RightsManager::canEditPwsPerm)
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class ResourceVoter implements VoterInterface
{
    private $em;
    private $repository;
    private $translator;
    private $specialActions;
    private $ut;
    private $maskManager;
    private $resourceManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "em"               = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "ut"               = @DI\Inject("claroline.security.utilities"),
     *     "maskManager"      = @DI\Inject("claroline.manager.mask_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        TranslatorInterface $translator,
        Utilities $ut,
        MaskManager $maskManager,
        ResourceManager $resourceManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->em = $em;
        $this->repository = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->translator = $translator;
        $this->specialActions = array('move', 'create', 'copy');
        $this->ut = $ut;
        $this->maskManager = $maskManager;
        $this->resourceManager = $resourceManager;
        $this->workspaceManager = $workspaceManager;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $object = $object instanceof AbstractResource ? $object->getResourceNode() : $object;

        if ($object instanceof ResourceCollection) {
            $errors = array();
            if (strtolower($attributes[0]) == 'create') {
                if ($targetWorkspace = $object->getResources()[0]) {
                    //there should be one one resource every time
                    //(you only create resource one at a time in a single directory
                    $targetWorkspace = $object->getResources()[0]->getWorkspace();

                    foreach ($object->getResources() as $resource) {
                        $errors = array_merge(
                            $errors,
                            $this->checkCreation($object->getAttribute('type'), $resource, $token, $targetWorkspace)
                        );
                    }
                } else {
                    return VoterInterface::ACCESS_GRANTED;
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

            $errors = array_unique($errors);
            $object->setErrors($errors);

            return VoterInterface::ACCESS_DENIED;
        } elseif ($object instanceof ResourceNode) {
            if (in_array($attributes[0], $this->specialActions)) {
                throw new \Exception('A ResourceCollection class must be used for this action.');
            }

            $errors = $this->checkAction($attributes[0], array($object), $token);

            return count($errors) === 0 && $object->isActive() ?
                VoterInterface::ACCESS_GRANTED :
                VoterInterface::ACCESS_DENIED;
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
     * @return bool
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
     * @param $action
     * @param array          $nodes
     * @param TokenInterface $token
     *
     * @return array
     *
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
        if ($haveSameWorkspace && $ws && $this->isWorkspaceManager($ws, $token)) {
            return array();
        }

        //the resource creator can do w/e he wants
        $timesCreator = 0;

        foreach ($nodes as $node) {
            if ($node->getCreator() === $token->getUser()) {
                ++$timesCreator;
            }
        }

        //but it only work if he's not usurpating a workspace role to see if everything is good
        if ($timesCreator == count($nodes) && !$this->isUsurpatingWorkspaceRole($token)) {
            return array();
        }

        //check if the action is possible on the node
        $errors = array();
        $action = strtolower($action);

        foreach ($nodes as $node) {
            $accessibleFrom = $node->getAccessibleFrom();
            $accessibleUntil = $node->getAccessibleUntil();
            $currentDate = new \DateTime();

            if ((is_null($accessibleFrom) || $currentDate >= $accessibleFrom) &&
                (is_null($accessibleUntil) || $currentDate <= $accessibleUntil) &&
                $node->isPublished() &&
                $node->isActive()) {
                $mask = $this->repository->findMaximumRights($this->ut->getRoles($token), $node);
                $type = $node->getResourceType();
                $decoder = $this->maskManager->getDecoder($type, $action);

                //gotta check
                if (!$decoder) {
                    return array('The permission '.$action.' does not exists for the type '.$type->getName());
                }

                $grant = $decoder ? $mask & $decoder->getValue() : 0;

                if ($decoder && $grant === 0) {
                    $errors[] = $this->getRoleActionDeniedMessage($action, $node->getPathForDisplay());
                }
            } else {
                $errors[] = $this->getRoleActionDeniedMessage($action, $node->getPathForDisplay());
            }
        }

        return $errors;
    }

    /**
     * Checks if a resource whose type is $type
     * can be created in the directory $resource by the $token.
     *
     * @param $type
     * @param ResourceNode                                     $node
     * @param TokenInterface                                   $token
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function checkCreation(
        $type,
        ResourceNode $node,
        TokenInterface $token,
        Workspace $workspace
    ) {
        $errors = array();

        //even the workspace manager can't break the file limit.
        $workspace = $node->getWorkspace();
        $isLimitExceeded = $this->resourceManager
            ->checkResourceLimitExceeded($workspace);

        if ($isLimitExceeded) {
            $currentCount = $this->workspaceManager->countResources($workspace);
            $errors[] = $this->translator
                ->trans(
                    'resource_limit_exceeded',
                    array('%current%' => $currentCount, '%max%' => $workspace->getMaxUploadResources()),
                    'platform'
                );
        }

        //if I am the manager, I can do whatever I want
        if ($this->isWorkspaceManager($workspace, $token)) {
            return $errors;
        }

        //otherwise we need to check
        $rightsCreation = $this->repository->findCreationRights($this->ut->getRoles($token), $node);

        if (!$this->canCreate($rightsCreation, $type)) {
            $errors[] = $this->translator
                ->trans(
                    'resource_creation_wrong_type',
                    array(
                        '%path%' => $node->getPathForDisplay(),
                        '%type%' => $this->translator->trans(
                            strtolower($type), array(), 'resource'
                        ),
                    ),
                    'platform'
                );
        }

        return $errors;
    }

    /**
     * Checks if the array of resources can be moved to the resource $parent
     * by the $token.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode                   $parent
     * @param array                                                                $nodes
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function checkMove(ResourceNode $parent, $nodes, TokenInterface $token)
    {
        $errors = [];

        //first I need to know if I can create
        foreach ($nodes as $node) {
            $type = $node->getResourceType()->getName();
            $errors = array_merge($errors, $this->checkCreation($type, $parent, $token, $parent->getWorkspace()));
        }

        //then I need to know if I can copy
        $errors = array_merge($errors, $this->checkCopy($parent, $nodes, $token));

        //and finally I need to know if I can delete
        $errors = array_merge($errors, $this->checkAction('DELETE', $nodes, $token));

        return $errors;
    }

    /**
     * Checks if the array of resources can be copied to the resource $parent
     * by the $token.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode                   $parent
     * @param array|\Claroline\CoreBundle\Library\Security\Voter\type              $nodes
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function checkCopy(ResourceNode $parent, array $nodes, TokenInterface $token)
    {
        //first I need to know if I can create what I want in the parent directory
        $errors = [];

        foreach ($nodes as $node) {
            $type = $node->getResourceType()->getName();
            $errors = array_merge($errors, $this->checkCreation($type, $parent, $token, $parent->getWorkspace()));
        }

        //then we need to know if we can copy
        $errors = array_merge($errors, $this->checkAction('COPY', $nodes, $token));

        return $errors;
    }

    public function getRoleActionDeniedMessage($action, $path)
    {
        return $this->translator
            ->trans(
                'resource_action_denied_message',
                array(
                    '%path%' => $path,
                    '%action%' => $action,
                    ),
                'platform'
            );
    }

    public function isWorkspaceManager(Workspace $workspace, TokenInterface $token)
    {
        $managerRoleName = 'ROLE_WS_MANAGER_'.$workspace->getGuid();

        return in_array($managerRoleName, $this->ut->getRoles($token)) ? true : false;
    }

    public function isUsurpatingWorkspaceRole(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role->getRole() === 'ROLE_USURPATE_WORKSPACE_ROLE') {
                return true;
            }
        }

        return false;
    }
}
