<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Security\Voter;

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AbstractRoleSubjectVoter extends AbstractVoter
{
    protected function checkPatchRoles(TokenInterface $token, AbstractRoleSubject $object, ObjectCollection $collection): int
    {
        if (!$collection->isInstanceOf(Role::class)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $action = $collection->getOption('action');

        $nonAuthorized = array_filter($collection->toArray(), function (Role $role) use ($token, $object, $action) {
            if ($this->isOrganizationManager($token, $object)) {
                return false;
            }

            if ($this->isToolGranted('ADMINISTRATE', 'community')) {
                return false;
            }

            $workspace = $role->getWorkspace();
            if ($workspace) {
                // If user is workspace manager then grant access
                if ($this->isGranted('ADMINISTRATE', $workspace)) {
                    return false;
                }

                if ($this->isToolGranted('REGISTER', 'community', $workspace)
                    || $this->isToolGranted('ADMINISTRATE', 'community', $workspace)) {
                    // If the user try to give the default role let him pass
                    if ($workspace->getDefaultRole() && $workspace->getDefaultRole()->getId() === $role->getId()) {
                        return false;
                    }

                    // Otherwise only allow modification of roles the current user owns
                    if (in_array($role->getName(), $token->getRoleNames())) {
                        return false;
                    }
                }

                if ('add' === $action) {
                    // If public registration is enabled and user try to get the default role, grant access
                    if ($workspace->getSelfRegistration() && $workspace->getDefaultRole()) {
                        if ($workspace->getDefaultRole()->getId() === $role->getId()) {
                            return false;
                        }
                    }
                } else {
                    // If public unregistration is enabled and user try to remove the role, grant access
                    if ($workspace->getSelfUnregistration() && $object instanceof User) {
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

    public function getClass(): string
    {
        return AbstractRoleSubject::class;
    }
}
