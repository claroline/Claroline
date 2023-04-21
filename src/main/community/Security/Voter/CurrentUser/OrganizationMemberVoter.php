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
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Denies all accesses to users which are not member of the same organizations as the subject.
 * It's automatically called on subjects which implements a `getOrganizations` method.
 */
class OrganizationMemberVoter implements VoterInterface, CacheableVoterInterface
{
    private OrganizationManager $organizationManager;

    public function __construct(OrganizationManager $organizationManager)
    {
        $this->organizationManager = $organizationManager;
    }
    /**
     * The OrganizationMemberVoter applies to any attributes.
     */
    public function supportsAttribute(string $attribute): bool
    {
        return true;
    }

    /**
     * The OrganizationMemberVoter only applies to subjects which are linked to organizations (aka. implements a `getOrganizations(): iterable` method).
     */
    public function supportsType(string $subjectType): bool
    {
        // apply voter to all the subjects having a creator
        return class_exists($subjectType) && method_exists($subjectType, 'getOrganizations');
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$token->getUser() instanceof User || !$this->organizationManager->isMember($token->getUser(), $subject->getOrganizations())) {
            // User is not part of the same organization, we can deny access now
            return self::ACCESS_DENIED;
        }

        // we do not grant any rights to organization members (it depends on the $subject)
        return self::ACCESS_ABSTAIN;
    }
}
