<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WorkspaceVoter extends AbstractVoter
{
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var WorkspaceRestrictionsManager */
    private $restrictionsManager;

    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceRestrictionsManager $restrictionsManager
    ) {
        $this->workspaceManager = $workspaceManager;
        $this->restrictionsManager = $restrictionsManager;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($this->isWorkspaceManaged($token, $object)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $collection = isset($options['collection']) ? $options['collection'] : null;

        //crud actions
        switch ($attributes[0]) {
            case self::VIEW:   return $this->checkView($token, $object);
            case self::CREATE: return $this->checkCreation($token);
            case self::EDIT:   return $this->checkEdit($token, $object);
            case self::DELETE: return $this->checkDelete($token, $object);
            case self::PATCH:  return $this->checkPatch($token, $object, $collection);
        }

        if (!$this->restrictionsManager->isStarted($object)
            || $this->restrictionsManager->isEnded($object)
            || !$this->restrictionsManager->isUnlocked($object)
            || !$this->restrictionsManager->isIpAuthorized($object)) {
            return VoterInterface::ACCESS_DENIED;
        }

        //then we do all the rest
        $toolName = isset($attributes[0]) && 'OPEN' !== $attributes[0] ?
            $attributes[0] :
            null;

        $action = isset($attributes[1]) ? strtolower($attributes[1]) : 'open';
        $accesses = $this->workspaceManager->getAccesses($token, [$object], $toolName, $action);
        //this is for the tools, probably change it later
        return isset($accesses[$object->getId()]) && true === $accesses[$object->getId()] ?
            VoterInterface::ACCESS_GRANTED :
            VoterInterface::ACCESS_DENIED;
    }

    //workspace creator handling ?
    private function checkCreation(TokenInterface $token)
    {
        if ($this->isWorkspaceCreator($token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit($token, Workspace $workspace)
    {
        if (!$this->isWorkspaceManaged($token, $workspace)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, Workspace $workspace)
    {
        // disallow deleting default models
        if (in_array($workspace->getCode(), ['default_personal', 'default_workspace'])) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (!$this->isWorkspaceManaged($token, $workspace)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView($token, Workspace $workspace)
    {
        if (!$this->isWorkspaceManaged($token, $workspace)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    /**
     * This is not done yet but later a user might be able to edit its roles/groups himself
     * and it should be checked here.
     *
     * @param ObjectCollection $collection
     *
     * @return int
     */
    private function checkPatch(TokenInterface $token, Workspace $workspace, ObjectCollection $collection = null)
    {
        //single property: no check now
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isWorkspaceManaged($token, $workspace)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        //maybe do something more complicated later
        return $this->isGranted(self::EDIT, $collection) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    private function isWorkspaceManaged(TokenInterface $token, Workspace $workspace)
    {
        return $this->workspaceManager->isManager($workspace, $token);
    }

    public function getClass()
    {
        return Workspace::class;
    }

    public function getSupportedActions()
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }

    protected function isWorkspaceCreator(TokenInterface $token)
    {
        return in_array(PlatformRoles::WS_CREATOR, $token->getRoleNames());
    }
}
