<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class GroupVoter extends AbstractRoleSubjectVoter
{
    public function getClass(): string
    {
        return Group::class;
    }

    /**
     * @param Group $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::OPEN:
                if ($this->isToolGranted(self::EDIT, 'community')
                    || $this->isToolGranted(self::ADMINISTRATE, 'community')
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                /** @var User $user */
                $user = $token->getUser();
                if ($user instanceof User && $user->hasGroup($object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
                if ($this->isToolGranted(self::EDIT, 'community')) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::ADMINISTRATE:
            case self::DELETE:
                if ($this->isToolGranted(self::ADMINISTRATE, 'community')) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::PATCH:
                if ($this->isToolGranted(self::EDIT, 'community')) {
                    return VoterInterface::ACCESS_DENIED;
                }

                if (!$collection) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($collection->isInstanceOf(Role::class)) {
                    return $this->checkPatchRoles($token, $object, $collection);
                }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE, self::PATCH];
    }
}
