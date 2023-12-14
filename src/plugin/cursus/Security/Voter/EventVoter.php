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
use Claroline\CursusBundle\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EventVoter extends AbstractVoter
{
    public const REGISTER = 'REGISTER';

    public function getClass(): string
    {
        return Event::class;
    }

    /**
     * @param Event $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $workspace = null;
        if ($object->getSession() && $object->getSession()->getWorkspace()) {
            $workspace = $object->getSession()->getWorkspace();
        }

        switch ($attributes[0]) {
            case self::CREATE: // EDIT right on tool
                if ($this->isToolGranted('EDIT', 'training_events', $workspace)
                    || $this->isToolGranted('EDIT', 'trainings')) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                if ($this->isToolGranted('EDIT', 'training_events', $workspace)
                    || ($object->getSession() && $this->isGranted('EDIT', $object->getSession()))) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
            case self::OPEN:
            case self::VIEW:
                if ($this->isToolGranted('OPEN', 'training_events', $workspace)
                    || ($object->getSession() && $this->isGranted('OPEN', $object->getSession()))) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::REGISTER:
                if ($this->isToolGranted('REGISTER', 'training_events', $workspace)
                    || ($object->getSession() && $this->isGranted('REGISTER', $object->getSession()))) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
