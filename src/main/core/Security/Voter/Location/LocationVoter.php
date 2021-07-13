<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter\Location;

use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class LocationVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::PATCH:
            case self::DELETE:
                if ($this->isToolGranted(self::EDIT, 'locations')) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            default:
                if ($this->isToolGranted($attributes[0], 'locations')) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return Location::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
