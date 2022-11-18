<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\LessonBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Icap\LessonBundle\Entity\Chapter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ChapterVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return Chapter::class;
    }

    /**
     * @param Chapter $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $parentNode = $object->getLesson()->getResourceNode();

        if ($parentNode) {
            switch ($attributes[0]) {
                case self::CREATE:
                case self::EDIT:
                case self::DELETE:
                    if ($this->isGranted('EDIT', $parentNode)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                    return VoterInterface::ACCESS_DENIED;

                case self::OPEN: // member of organization & OPEN right on tool
                case self::VIEW:
                    if ($this->isGranted('OPEN', $parentNode)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                    return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
