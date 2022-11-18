<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Security\Voter\Resource;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SectionCommentVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
        }

        return $this->isGranted($attributes, $object->getSection()->getResourceNode());
    }

    public function getClass(): string
    {
        return SectionComment::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }

    private function checkEdit(TokenInterface $token, SectionComment $sectionComment): int
    {
        $section = $sectionComment->getSection();
        $resourceNode = $section->getResourceNode();
        $user = $token->getUser();
        $isAnon = !$user instanceof User;

        if (!$isAnon &&
            $this->isGranted(self::OPEN, $resourceNode) &&
            $section->isCommentsAllowed() &&
            $sectionComment->getUser()->getUuid() === $user->getUuid() &&
            (
                AudioParams::MANAGER_TYPE === $section->getType() ||
                (
                    AudioParams::USER_TYPE === $section->getType() &&
                    $section->getUser()->getUuid() === $user->getUuid()
                )
            )
        ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
