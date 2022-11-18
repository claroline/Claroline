<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\BigBlueButtonBundle\Entity\Recording;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class RecordingVoter extends AbstractVoter
{
    /**
     * @param Recording $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if ($this->isGranted(self::ADMINISTRATE, $object->getMeeting()) || $this->isGranted($attributes, $object->getMeeting())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return Recording::class;
    }
}
