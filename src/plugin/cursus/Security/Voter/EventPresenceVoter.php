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

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CursusBundle\Entity\EventPresence;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EventPresenceVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return EventPresence::class;
    }

    /**
     * @param EventPresence $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $isManager = $this->isGranted('EDIT', $object->getEvent()) || $this->isGranted('REGISTER', $object->getEvent());

        switch ($attributes[0]) {
            case self::DELETE:
                if ($isManager) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                // no break
            case self::OPEN:
            case self::EDIT:
                if ($isManager || $token->getUser() === $object->getUser()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions(): ?array
    {
        return [self::OPEN, self::EDIT, self::DELETE];
    }
}
