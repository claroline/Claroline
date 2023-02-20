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
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AnnouncementVoter extends AbstractVoter
{
    /**
     * @param Announcement $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $resourceNode = $object->getAggregate()->getResourceNode();

        switch ($attributes[0]) {
            case self::OPEN:
                if ($this->isGranted('OPEN', $resourceNode)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
                if ($this->isGranted('CREATE-ANNOUNCE', $resourceNode)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
                // creator can edit its own announcements
                if ($token->getUser() instanceof User && $object->getCreator() && $token->getUser()->getId() === $object->getCreator()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                // everyone who can edit the resource node can edit its announcements
                if ($this->isGranted('EDIT', $resourceNode)) {
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
        return [self::OPEN, self::CREATE, self::EDIT, self::DELETE];
    }
}
