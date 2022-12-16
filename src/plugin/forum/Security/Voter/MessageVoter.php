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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\ForumBundle\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MessageVoter extends AbstractVoter
{
    const POST = 'POST';

    public function getClass(): string
    {
        return Message::class;
    }

    /**
     * @param Message $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if (empty($object->getForum())) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        switch ($attributes[0]) {
            case self::CREATE:
                return $this->checkCreate($object, $token);
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($object, $token);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function checkCreate(Message $message, TokenInterface $token): int
    {
        $subject = $message->getSubject();

        if ($token->getUser() instanceof User && $this->isGranted('OPEN', $subject)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(Message $message, TokenInterface $token): int
    {
        $forum = $message->getForum();

        if ($this->isGranted('EDIT', $forum->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($token->getUser() instanceof User && $message->getCreator() && $message->getCreator()->getId() === $token->getUser()->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getSupportedActions(): array
    {
        return [self::CREATE, self::EDIT, self::DELETE];
    }
}
