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

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TeamVoter extends AbstractVoter
{
    /**
     * @param Team $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if ($this->isGranted(['community', 'edit'], $object->getWorkspace())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        switch ($attributes[0]) {
            case self::OPEN:
            case self::VIEW:
                if ($this->isGranted(['community', 'open'], $object->getWorkspace())) {
                    if ($object->isSelfRegistration() || ($token->getUser() instanceof User && $object->hasUser($token->getUser()))) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass(): string
    {
        return Team::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
