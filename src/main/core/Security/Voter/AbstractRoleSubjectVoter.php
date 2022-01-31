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
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AbstractRoleSubjectVoter extends AbstractVoter
{
    protected function checkPatchRoles(TokenInterface $token, AbstractRoleSubject $object, ObjectCollection $collection): int
    {
        if (!$collection->isInstanceOf(Role::class)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if we can add a workspace (this block is mostly a c/c from RoleVoter)
        $nonAuthorized = array_filter($collection->toArray(), function (Role $role) use ($token, $object) {
            $workspace = $role->getWorkspace();
            if ($workspace) {
                if ($this->isGranted(['community', 'create_user'], $workspace)) {
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

            // we can only add platform roles to users if we have that platform role or are organization manager
            if ($this->isOrganizationManager($token, $object)) {
                return false;
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

    public function getClass(): string
    {
        return AbstractRoleSubject::class;
    }
}
