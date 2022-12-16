<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Subject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SubjectVoter extends AbstractVoter
{
    const POST = 'POST';

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::OPEN:
                return $this->checkOpen($object);
            case self::CREATE:
                return $this->checkCreate($object);
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($object, $token);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkOpen(Subject $subject): int
    {
        $forum = $subject->getForum();

        if ($this->isGranted('OPEN', $forum->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkCreate(Subject $subject): int
    {
        $forum = $subject->getForum();

        if ($this->isGranted(self::POST, $forum->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(Subject $subject, TokenInterface $token): int
    {
        $forum = $subject->getForum();

        if ($this->isGranted('EDIT', $forum->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($token->getUser() instanceof User && $subject->getCreator() && $subject->getCreator()->getId() === $token->getUser()->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return Subject::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::DELETE, self::POST];
    }
}
