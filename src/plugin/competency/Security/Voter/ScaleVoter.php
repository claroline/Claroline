<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use HeVinci\CompetencyBundle\Entity\Scale;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ScaleVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
            case self::OPEN:
            case self::VIEW:
                return $this->hasAdminToolAccess($token, 'competencies') ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return Scale::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
