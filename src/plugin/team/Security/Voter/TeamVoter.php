<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\TeamBundle\Entity\Team;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TeamVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::OPEN:
            case self::VIEW:
                if ($this->isGranted(['claroline_team_tool', 'open'], $object->getWorkspace())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                if ($this->isGranted(['claroline_team_tool', 'edit'], $object->getWorkspace())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return Team::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
