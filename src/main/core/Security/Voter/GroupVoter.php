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

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class GroupVoter extends AbstractRoleSubjectVoter
{
    /**
     * @param Group $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::OPEN:
            case self::VIEW:
                return $this->checkView($token, $object);
            case self::ADMINISTRATE:
            case self::EDIT:
                return $this->checkEdit($token, $object);
            case self::DELETE:
                return $this->checkDelete($token, $object);
            case self::PATCH:
                return $this->checkPatch($token, $object, $collection);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkEdit($token, Group $group)
    {
        if (!$this->isOrganizationManager($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, Group $group)
    {
        if (!$this->isOrganizationManager($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView($token, Group $group)
    {
        /** @var User $user */
        $user = $token->getUser();
        if ($user && $user->hasGroup($group)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkPatch(TokenInterface $token, Group $group, ObjectCollection $collection = null): int
    {
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($collection->isInstanceOf(Role::class)) {
            return $this->checkPatchRoles($token, $collection);
        }

        return $this->checkEdit($token, $group);
    }

    public function getClass(): string
    {
        return Group::class;
    }

    /**
     * @return array
     */
    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE, self::PATCH];
    }
}
