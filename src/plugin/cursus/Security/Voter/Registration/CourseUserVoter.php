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
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CourseUserVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return CourseUser::class;
    }

    /**
     * @param CourseUser $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $course = $object->getCourse();

        // managers of the session registrations can do everything
        if ($this->isGranted('REGISTER', $course)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // if we are not the owner of the registration we can do nothing
        if (!$token->getUser() instanceof User || $token->getUser()->getId() !== $object->getUser()->getId()) {
            return VoterInterface::ACCESS_DENIED;
        }

        switch ($attributes[0]) {
            case self::CREATE:
                // if self registration is enabled, users can create registration for themselves
                if ($course->getPublicRegistration() && $course->getPendingRegistrations()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
            case self::EDIT:
                return VoterInterface::ACCESS_GRANTED;

            case self::DELETE:
                if ($course->getPublicUnregistration()) {
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
