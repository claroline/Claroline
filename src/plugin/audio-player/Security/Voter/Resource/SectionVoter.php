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
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SectionVoter extends AbstractVoter
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

        return $this->isGranted($attributes, $object->getResourceNode());
    }

    public function getClass(): string
    {
        return Section::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }

    private function checkCreate(TokenInterface $token, Section $section)
    {
        $resourceNode = $section->getResourceNode();
        $user = $token->getUser();

        if ((AudioParams::MANAGER_TYPE === $section->getType() && $this->isGranted(self::EDIT, $resourceNode)) ||
            (AudioParams::USER_TYPE === $section->getType() && $user instanceof User && $this->isGranted(self::OPEN, $resourceNode))
        ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(TokenInterface $token, Section $section)
    {
        $resourceNode = $section->getResourceNode();
        $sectionUser = $section->getUser();
        $user = $token->getUser();

        if ((AudioParams::MANAGER_TYPE === $section->getType() && $this->isGranted(self::EDIT, $resourceNode)) ||
            (
                AudioParams::USER_TYPE === $section->getType() &&
                $user instanceof User &&
                $this->isGranted(self::OPEN, $resourceNode) &&
                $sectionUser &&
                $sectionUser->getUuid() === $user->getUuid()
            )
        ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
