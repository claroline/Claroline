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

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * This voter checks if the current user is allowed to usurp another user's account.
 */
class UserSwitchVoter extends Voter
{
    private Security $security;

    public function __construct(
        Security $security
    ) {
        $this->security = $security;
    }

    public function supportsAttribute(string $attribute): bool
    {
        return 'ROLE_ALLOWED_TO_SWITCH' === $attribute;
    }

    public function supportsType(string $subjectType): bool
    {
        // you can't use a simple User::class === $subjectType comparison
        // here because the given subject type could be the proxy class used
        // by Doctrine when creating the entity object
        return is_a($subjectType, User::class, true);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $this->supportsAttribute($attribute) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ADMINISTRATE', $subject);
    }
}
