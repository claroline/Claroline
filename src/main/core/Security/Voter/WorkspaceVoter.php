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
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WorkspaceVoter extends AbstractVoter implements CacheableVoterInterface
{
    private WorkspaceManager $workspaceManager;
    private WorkspaceRestrictionsManager $restrictionsManager;

    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceRestrictionsManager $restrictionsManager
    ) {
        $this->workspaceManager = $workspaceManager;
        $this->restrictionsManager = $restrictionsManager;
    }

    public function getClass(): string
    {
        return Workspace::class;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {

    }

    /**
     * Return false if your voter doesn't support the given attribute. Symfony will cache
     * that decision and won't call your voter again for that attribute.
     */
    public function supportsAttribute(string $attribute): bool
    {
        return true;
    }

    public function supportsType(string $subjectType): bool
    {
        // you can't use a simple User::class === $subjectType comparison
        // here because the given subject type could be the proxy class used
        // by Doctrine when creating the entity object
        return is_a($subjectType, Workspace::class, true);
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::VIEW:   return $this->checkView($token, $object);
            case self::CREATE: return $this->checkCreation($token);
            case self::EDIT:   return $this->checkEdit($token, $object);
            case self::DELETE: return $this->checkDelete($token, $object);
            case self::PATCH:  return $this->checkPatch($token, $object, $collection);
        }

        if ($this->isWorkspaceManaged($token, $object)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if (!$this->restrictionsManager->isStarted($object)
            || $this->restrictionsManager->isEnded($object)
            || !$this->restrictionsManager->isUnlocked($object)
            || !$this->restrictionsManager->isIpAuthorized($object)) {
            return VoterInterface::ACCESS_DENIED;
        }

        $toolName = isset($attributes[0]) && 'OPEN' !== $attributes[0] ?
            $attributes[0] :
            null;

        $action = isset($attributes[1]) ? strtolower($attributes[1]) : 'open';

        if ($this->workspaceManager->hasAccess($object, $token, $toolName, $action)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkCreation(TokenInterface $token): int
    {
        if ($this->isWorkspaceCreator($token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit($token, Workspace $workspace): int
    {
        if (!$this->isWorkspaceManaged($token, $workspace)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, Workspace $workspace): int
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

    private function checkView($token, Workspace $workspace): int
    {
        if (!$this->isWorkspaceManaged($token, $workspace)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkPatch(TokenInterface $token, Workspace $workspace, ObjectCollection $collection = null): int
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

    private function isWorkspaceManaged(TokenInterface $token, Workspace $workspace): bool
    {
        return $this->workspaceManager->isManager($workspace, $token);
    }

    private function isWorkspaceCreator(TokenInterface $token): bool
    {
        return in_array(PlatformRoles::WS_CREATOR, $token->getRoleNames());
    }

    public function getSupportedActions(): ?array
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }
}
