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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Collection\UserCollection;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class UserVoter implements VoterInterface
{
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const VIEW = 'view';

    private $ch;
    private $om;
    private $userAdminTool;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "ch"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "userManager" = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $ch,
        UserManager $userManager
    ) {
        $this->om = $om;
        $this->ch = $ch;
        $this->userManager = $userManager;
    }

    //ROLE_ADMIN can always do anything, so we don't have to check that.
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object instanceof User && !$object instanceof UserCollection) {
            return VoterInterface::ACCESS_ABSTAIN;
        }
        $users = $object instanceof UserCollection ? $object->getUsers() : [$object];
        $action = strtolower($attributes[0]);

        switch ($action) {
            case self::VIEW:   return $this->checkView($users);
            case self::CREATE: return $this->checkCreation($users);
            case self::EDIT:   return $this->checkEdit($token, $users);
            case self::DELETE: return $this->checkDelete($token, $users);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkCreation($users)
    {
        //the we can create user. Case closed
        if ($this->ch->getParameter('allow_self_registration')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        //maybe more tests
    }

    private function checkEdit($token, $users)
    {
        foreach ($users as $user) {
            if (!$this->isOrganizationManager($token, $user)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView($token, $users)
    {
        foreach ($users as $user) {
            if (!$this->isOrganizationManager($token, $user)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete($token, $users)
    {
        foreach ($users as $user) {
            if (!$this->isOrganizationManager($token, $user)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    //I should find a way to speed that up
    private function isOrganizationManager(TokenInterface $token, User $user)
    {
        $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
        $userOrganizations = $user->getOrganizations();

        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($userOrganizations as $userOrganization) {
                if ($userOrganization === $adminOrganization) {
                    return true;
                }
            }
        }

        return false;
    }

    public function supportsClass($class)
    {
        return true;
        //as a reminder for the 3.0 voter
        //return array('Claroline\CoreBundle\Entity\User');
    }

    public function supportsAttribute($attribute)
    {
        return true;
        //as a reminder for the 3.0 voter
        //return array(self::CREATE, self::EDIT, self::DELETE);
    }
}
