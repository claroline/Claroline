<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Security\Voter;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AnnouncementVoter extends AbstractVoter
{
    /**
     * @param Announcement $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::OPEN:
                if ($this->isToolGranted('OPEN', 'announcement', $object->getWorkspace())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
                if ($this->isToolGranted('CREATE', 'announcement', $object->getWorkspace())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
            case self::ADMINISTRATE:
                if ($this->isToolGranted('EDIT', 'announcement', $object->getWorkspace())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass(): string
    {
        return Announcement::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::ADMINISTRATE, self::EDIT, self::DELETE];
    }
}
