<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\CursusBundle\Entity\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SessionVoter extends AbstractVoter
{
    const REGISTER = 'REGISTER';

    public function getClass()
    {
        return Session::class;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $granted = null;
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                $granted = $this->isGranted('EDIT', $object->getCourse());
                break;
            case self::OPEN:
                $granted = $this->isGranted('OPEN', $object->getCourse());
                break;
            case self::VIEW:
                $granted = $this->isGranted('VIEW', $object->getCourse());
                break;
            case self::REGISTER:
                $granted = $this->isGranted('REGISTER', $object->getCourse());
                break;
        }

        if ($granted) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif (false === $granted) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::REGISTER];
    }
}
