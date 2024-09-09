<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Security\Voter\CurrentUser;

use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * This voter grants access to admin users, whenever the attribute or the
 * class is. This means that administrators are seen by the AccessDecisionManager
 * as if they have all the possible roles and permissions on every object or class.
 */
class AdministratorVoter implements VoterInterface, CacheableVoterInterface
{
    public function supportsAttribute(string $attribute): bool
    {
        // ROLE_USURPATE_WORKSPACE_ROLE check is here to avoid applying the admin bypass when browsing a workspace in impersonation mode
        return 'ROLE_USURPATE_WORKSPACE_ROLE' !== $attribute;
    }

    public function supportsType(string $subjectType): bool
    {
        return true;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (in_array(PlatformRoles::ADMIN, $token->getRoleNames())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
