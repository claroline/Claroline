<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Security\Voter;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\DropZoneBundle\Entity\DropComment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DropCommentVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::OPEN:
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                return $this->canEditResource($object) || $this->isOwner($token, $object) ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return DropComment::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE];
    }

    private function canEditResource(DropComment $comment)
    {
        $resourceNode = $comment->getDrop()->getDropzone()->getResourceNode();

        return $this->isGranted(self::EDIT, $resourceNode);
    }

    private function isOwner(TokenInterface $token, DropComment $comment)
    {
        $isOwner = false;
        $user = $token->getUser();

        if ($user instanceof User) {
            $drop = $comment->getDrop();
            $dropUsers = $drop->getUsers();

            if ($drop->getUser()) {
                $dropUsers[] = $drop->getUser();
            }
            foreach ($dropUsers as $dropUser) {
                if ($dropUser->getUuid() === $user->getUuid()) {
                    $isOwner = true;
                    break;
                }
            }
        }

        return $isOwner;
    }
}
