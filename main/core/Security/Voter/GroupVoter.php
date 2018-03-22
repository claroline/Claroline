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
use Claroline\CoreBundle\Security\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class GroupVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::CREATE: return $this->checkCreation($token);
            case self::EDIT:   return $this->checkEdit($token, $object);
            case self::DELETE: return $this->checkDelete($token, $object);
            case self::VIEW:   return $this->checkView($token, $object);
            case self::PATCH:  return $this->checkPatch($token, $object, $collection);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkCreation(TokenInterface $token)
    {
        return $this->hasAdminToolAccess($token, 'user_management') ?
             VoterInterface::ACCESS_GRANTED :
             VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit($token, Group $group)
    {
        if (!$this->isGroupManaged($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, Group $group)
    {
        if (!$this->isGroupManaged($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView($token, Group $group)
    {
        if (!$this->isGroupManaged($token, $group)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    /**
     * This is not done yet but later a user might be able to edit its roles/groups himself
     * and it should be checked here.
     *
     * @param TokenInterface   $token
     * @param User             $user
     * @param ObjectCollection $collection
     *
     * @return int
     */
    private function checkPatch(TokenInterface $token, Group $group, ObjectCollection $collection = null)
    {
        //single property: no check now
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        //we can only add platform roles to users if we have that platform role
        //require dedicated unit test imo
        if ($collection->isInstanceOf('Claroline\CoreBundle\Entity\Role')) {
            $currentRoles = array_map(function ($role) {
                return $role->getRole();
            }, $token->getRoles());

            if (count(array_filter((array) $collection, function ($role) use ($currentRoles) {
                return Role::PLATFORM_ROLE === $role && !in_array($role->getName(), $currentRoles);
            })) > 0) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        if ($this->isGroupManaged($token, $group)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        //maybe do something more complicated later
        return $this->isGranted(self::EDIT, $collection) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    private function isGroupManaged(TokenInterface $token, Group $group)
    {
        $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
        $groupOrganizations = $group->getOrganizations();

        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($groupOrganizations as $groupOrganization) {
                if ($groupOrganization === $adminOrganization) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Group';
    }

    /**
     * @return array
     */
    public function getSupportedActions()
    {
        return[self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
