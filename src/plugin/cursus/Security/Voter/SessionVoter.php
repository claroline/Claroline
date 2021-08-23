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
    const SELF_REGISTER = 'SELF_REGISTER';

    public function getClass()
    {
        return Session::class;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                return $this->isGranted('EDIT', $object->getCourse());
            case self::OPEN:
                return $this->isGranted('OPEN', $object->getCourse());
            case self::VIEW:
                return $this->isGranted('VIEW', $object->getCourse());

            case self::SELF_REGISTER:
                return $this->isGranted('SELF_REGISTER', $object->getCourse());
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
