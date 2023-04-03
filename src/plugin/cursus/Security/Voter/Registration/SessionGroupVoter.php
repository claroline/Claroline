<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Security\Voter\Registration;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SessionGroupVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return SessionGroup::class;
    }

    /**
     * @param SessionGroup $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $session = $object->getSession();

        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                // managers of the session registrations can do everything
                if ($this->isGranted('REGISTER', $session)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions(): array
    {
        return [self::CREATE, self::EDIT, self::DELETE];
    }
}
