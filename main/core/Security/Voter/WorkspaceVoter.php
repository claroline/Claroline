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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\AbstractVoter;
use Claroline\CoreBundle\Security\PlatformRoles;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class WorkspaceVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($object->getCreator() === $token->getUser() || $this->isWorkspaceManaged($token, $object)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $collection = isset($options['collection']) ? $options['collection'] : null;

        //crud actions
        switch ($attributes[0]) {
            case self::VIEW:   return $this->checkView($token, $object);
            case self::CREATE: return $this->checkCreation();
            case self::EDIT:   return $this->checkEdit($token, $object);
            case self::DELETE: return $this->checkDelete($token, $object);
            case self::PATCH:  return $this->checkPatch($token, $object, $collection);
        }

        //check the expiration date first
        $now = new \DateTime();
        if ($object->getEndDate()) {
            if ($now->getTimeStamp() > $object->getEndDate()->getTimeStamp()) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        //then we do all the rest
        $toolName = isset($attributes[0]) && 'OPEN' !== $attributes[0] ?
            $attributes[0] :
            null;

        $wm = $this->getContainer()->get('claroline.manager.workspace_manager');

        $action = isset($attributes[1]) ? strtolower($attributes[1]) : 'open';
        $accesses = $wm->getAccesses($token, [$object], $toolName, $action);
        //this is for the tools, probably change it later
        return isset($accesses[$object->getId()]) && true === $accesses[$object->getId()] ?
            VoterInterface::ACCESS_GRANTED :
            VoterInterface::ACCESS_DENIED;
    }

    //workspace creator handling ?
    private function checkCreation(TokenInterface $token)
    {
        return $this->hasAdminToolAccess($token, 'workspace_management') || $this->isWorkspaceCreator($token) ?
             VoterInterface::ACCESS_GRANTED :
             VoterInterface::ACCESS_DENIED;
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
     * @param TokenInterface   $token
     * @param User             $user
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
        if (!$token->getUser() instanceof User) {
            return false;
        }

        $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
        $workspaceOrganizations = $workspace->getOrganizations();

        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($workspaceOrganizations as $workspaceOrganization) {
                if ($workspaceOrganization === $adminOrganization) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\Workspace';
    }

    public function getSupportedActions()
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }

    protected function isWorkspaceCreator(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if (PlatformRoles::WS_CREATOR === $role->getRole()) {
                return true;
            }
        }

        return false;
    }
}
