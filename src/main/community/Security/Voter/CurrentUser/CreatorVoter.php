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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Grants all accesses to the user who has created the subject.
 * It's automatically called on subjects which implements a `getCreator` method.
 */
class CreatorVoter implements VoterInterface, CacheableVoterInterface
{
    /**
     * The CreatorVoter applies to any attributes.
     */
    public function supportsAttribute(string $attribute): bool
    {
        return true;
    }

    /**
     * The CreatorVoter only applies to subjects which have a creator (aka. implements a `getCreator(): User` method).
     */
    public function supportsType(string $subjectType): bool
    {
        // apply voter to all the subjects having a creator
        return class_exists($subjectType) && method_exists($subjectType, 'getCreator');
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if ($subject->getCreator() && $token->getUser() && $subject->getCreator() === $token->getUser()) {
            return self::ACCESS_GRANTED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
