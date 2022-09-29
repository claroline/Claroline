<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceCommentVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::OPEN:
            case self::VIEW:
                if ($this->isGranted('OPEN', $object->getResourceNode())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return ResourceComment::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE];
    }

    private function checkEdit(TokenInterface $token, ResourceComment $comment)
    {
        $user = $token->getUser();
        $commentUser = $comment->getUser();

        if ($this->isGranted('EDIT', $comment->getResourceNode()) ||
            ($user instanceof User && $commentUser && $user->getUuid() === $commentUser->getUuid())
        ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
