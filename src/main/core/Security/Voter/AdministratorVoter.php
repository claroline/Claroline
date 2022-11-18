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

use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * This voter grants access to admin users, whenever the attribute or the
 * class is. This means that administrators are seen by the AccessDecisionManager
 * as if they have all the possible roles and permissions on every object or class.
 */
class AdministratorVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $object, array $attributes): int
    {
        if ($this->isAdmin($token) && !$this->isUsurpingWorkspaceRole($token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function isAdmin(TokenInterface $token): bool
    {
        return in_array(PlatformRoles::ADMIN, $token->getRoleNames());
    }

    private function isUsurpingWorkspaceRole(TokenInterface $token): bool
    {
        return in_array('ROLE_USURPATE_WORKSPACE_ROLE', $token->getRoleNames());
    }
}
