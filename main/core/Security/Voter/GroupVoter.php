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
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class GroupVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::ADMINISTRATE:
            case self::EDIT:
                return $this->checkEdit($token, $object);
            case self::DELETE:
                return $this->checkDelete($token, $object);
            case self::VIEW:
                return $this->checkView($token, $object);
            case self::PATCH:
                return $this->checkPatch($token, $object, $collection);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkEdit($token, Group $group)
    {
        if (!$this->isOrganizationManager($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, Group $group)
    {
        if (!$this->isOrganizationManager($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView($token, Group $group)
    {
        if (!$this->isOrganizationManager($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkPatch(TokenInterface $token, Group $group, ObjectCollection $collection = null): int
    {
        //single property: no check now
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // we can only add platform roles to users if we have that platform role
        if ($collection->isInstanceOf(Role::class)) {
            // check if we can add a workspace (this block is mostly a c/c from RoleVoter)
            $nonAuthorized = array_filter($collection->toArray(), function (Role $role) use ($token) {
                $workspace = $role->getWorkspace();
                if ($workspace) {
                    if ($this->isGranted(['community', 'edit'], $workspace)) {
                        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
                        // If user is workspace manager then grant access
                        if ($workspaceManager->isManager($workspace, $token)) {
                            return false;
                        }

                        // Otherwise only allow modification of roles the current user owns
                        if (in_array($role->getName(), $token->getRoleNames())) {
                            return false;
                        }
                    }

                    // If public registration is enabled and user try to get the default role, grant access
                    if ($workspace->getSelfRegistration() && $workspace->getDefaultRole()) {
                        if ($workspace->getDefaultRole()->getId() === $role->getId()) {
                            return false;
                        }
                    }

                    // user has no community right on the workspace he cannot add anything
                    return true;
                }

                if (Role::PLATFORM_ROLE === $role->getType() && in_array($role->getName(), $token->getRoleNames())) {
                    return false;
                }

                return true;
            });

            if (0 < count($nonAuthorized)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isOrganizationManager($token, $group)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        //maybe do something more complicated later
        if ($this->isGranted(self::EDIT, $collection)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Group::class;
    }

    /**
     * @return array
     */
    public function getSupportedActions()
    {
        return [self::VIEW, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE, self::PATCH];
    }
}
