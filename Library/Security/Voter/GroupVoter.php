<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Entity\Group;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class GroupVoter implements VoterInterface
{
        /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "groupManager" = @DI\Inject("claroline.manager.group_manager")
     * })
     */
    public function __construct(
        ObjectManager $om, 
        GroupManager $groupManager
    )
    {
        $this->om = $om;
        $this->groupManager = $groupManager;
        //search from repository
        $this->userAdminTool = $om->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')
            ->findOneByName('user_management');
    }

    //ROLE_ADMIN can always do anything, so we don't have to check that.
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        //this line won't be usefull later
        if (!$object instanceof Group) return VoterInterface::ACCESS_ABSTAIN;

        $action = strtolower($attributes[0]);

        switch ($action) {
            case self::CREATE: return $this->checkCreation($object);
            case self::EDIT:   return $this->checkEdit($token, $object);
            case self::DELETE: return $this->checkDelete($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkCreation(Group $group)
    {
        //the we can create user. Case closed
        if ($ch->getParameter('allow_self_registration')) return VoterInterface::ACCESS_GRANTED;

        //maybe more tests
    }

    private function checkEdit($token, Group $group)
    {
        return $this->isOrganizationManager($token, $group) ? VoterInterface::ACCESS_GRANTED: VoterInterface::ACCESS_DENIED;
    }

    private function checkDekete($token, Group $group)
    {
        return $this->isOrganizationManager($token, $group) ? VoterInterface::ACCESS_GRANTED: VoterInterface::ACCESS_DENIED;
    }

    private function isOrganizationManager(TokenInterface $token, User $group)
    {
        $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
        $userOrganizations = $group->getOrganizations();

        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($userOrganizations as $userOrganization) {
                if ($userOrganization === $adminOrganization) return true;
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