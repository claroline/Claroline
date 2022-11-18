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

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Revision;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class RevisionVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
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

    public function getClass(): string
    {
        return Revision::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE];
    }

    private function canEditResource(Revision $revision): bool
    {
        $resourceNode = $revision->getDrop()->getDropzone()->getResourceNode();

        return $this->isGranted(self::EDIT, $resourceNode);
    }

    private function isOwner(TokenInterface $token, Revision $revision): bool
    {
        $isOwner = false;
        /** @var User|string $user */
        $user = $token->getUser();

        if ('anon.' !== $user) {
            $drop = $revision->getDrop();
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
