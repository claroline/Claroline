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
use Claroline\ForumBundle\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MessageVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::CREATE:
                return $this->checkCreate($token, $object);
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
        }

        return self::ACCESS_ABSTAIN;
    }

    public function checkCreate(TokenInterface $token, Message $message): int
    {
        $forum = $message->getForum();

        if ($forum && $this->isGranted(self::OPEN, $forum->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function checkEdit(TokenInterface $token, Message $message): int
    {
        if ($token->getUser() instanceof User && $token->getUser() === $message->getCreator()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return Message::class;
    }

    public function getSupportedActions(): array
    {
        return [self::CREATE, self::EDIT, self::DELETE];
    }
}
