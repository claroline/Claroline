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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Library\Security\Collection\GroupCollection;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class GroupVoter implements VoterInterface
{
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const VIEW = 'view';

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "groupManager" = @DI\Inject("claroline.manager.group_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        GroupManager $groupManager
    ) {
        $this->om = $om;
        $this->groupManager = $groupManager;
    }

    //ROLE_ADMIN can always do anything, so we don't have to check that.
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object instanceof Group && !$object instanceof GroupCollection) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $groups = $object instanceof GroupCollection ? $object->getGroups() : [$object];
        $action = strtolower($attributes[0]);

        switch ($action) {
            case self::CREATE: return $this->checkCreation($token);
            case self::EDIT:   return $this->checkEdit($token, $groups);
            case self::DELETE: return $this->checkDelete($token, $groups);
            case self::VIEW:   return $this->checkView($token, $groups);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkCreation(TokenInterface $token)
    {
        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneBy(['name' => 'user_management']);

        $roles = $tool->getRoles();
        $tokenRoles = $token->getRoles();

        foreach ($tokenRoles as $tokenRole) {
            foreach ($roles as $role) {
                if ($role->getRole() === $tokenRole->getRole()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit($token, $groups)
    {
        foreach ($groups as $group) {
            if (!$this->isOrganizationManager($token, $group)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, $groups)
    {
        foreach ($groups as $group) {
            if (!$this->isOrganizationManager($token, $group)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView($token, $groups)
    {
        foreach ($groups as $group) {
            if (!$this->isOrganizationManager($token, $group)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function isOrganizationManager(TokenInterface $token, Group $group)
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

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }
}
