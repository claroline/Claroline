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
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role as BaseRole;

class UserVoter extends AbstractVoter
{
    /**
     * @param TokenInterface $token
     * @param mixed          $object
     * @param array          $attributes
     * @param array          $options
     *
     * @return int
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::CREATE: return $this->checkCreate($token, $object);
            case self::VIEW:   return $this->checkView($token, $object);
            case self::EDIT:   return $this->checkEdit($token, $object);
            case self::DELETE: return $this->checkDelete($token, $object);
            case self::PATCH:  return $this->checkPatch($token, $object, $collection);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * We currently check this manually inside the Controller. This should change and be checked here.
     *
     * @param TokenInterface $token
     * @param User           $user
     *
     * @return int
     */
    private function checkEdit(TokenInterface $token, User $user)
    {
        //the user can edit himself too.
        //He just can add roles and stuff and this should be checked later
        if ($token->getUser() === $user) {
            return true;
        }

        return $this->isOrganizationManager($token, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    /**
     * We currently check this manually inside the Controller. This should change and be checked here.
     *
     * @param TokenInterface $token
     * @param User           $user
     *
     * @return int
     */
    private function checkView(TokenInterface $token, User $user)
    {
        return $this->isOrganizationManager($token, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    /**
     * @param TokenInterface $token
     * @param User           $user
     *
     * @return int
     */
    private function checkDelete(TokenInterface $token, User $user)
    {
        return $this->isOrganizationManager($token, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
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
    private function checkPatch(TokenInterface $token, User $user, ObjectCollection $collection = null)
    {
        //single property: no check now
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($collection->isInstanceOf('Claroline\CoreBundle\Entity\Role')) {
            // check if we can add a workspace (this block is mostly a c/c from RoleVoter)
            $nonAuthorized = array_filter($collection->toArray(), function (Role $role) use ($token) {
                if ($role->getWorkspace() && $this->isGranted(['users', 'edit'], $role->getWorkspace())) {
                    $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
                    // If user is workspace manager then grant access
                    if ($workspaceManager->isManager($role->getWorkspace(), $token)) {
                        return false;
                    }

                    // If role to be removed is not an administrate role then grant access
                    $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
                    $wsRoles = $roleManager->getWorkspaceNonAdministrateRoles($role->getWorkspace());
                    if (in_array($role, $wsRoles)) {
                        return false;
                    }
                }

                return true;
            });

            if (0 === count($nonAuthorized)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            // we can only add platform roles to users if we have that platform role
            // require dedicated unit test imo
            $currentRoles = array_map(function (BaseRole $role) {
                return $role->getRole();
            }, $token->getRoles());
            if (count(array_filter($collection->toArray(), function (Role $role) use ($currentRoles) {
                // search for not allowed roles
                return Role::PLATFORM_ROLE === $role->getType() && !in_array($role->getName(), $currentRoles);
            })) > 0) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        if ($this->isOrganizationManager($token, $user)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // maybe do something more complicated later
        return $this->isGranted(self::EDIT, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    private function checkCreate(TokenInterface $token, User $user)
    {
        if ($this->hasAdminToolAccess($token, 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isOrganizationManager($token, $user)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        /** @var PlatformConfigurationHandler $config */
        $config = $this->getContainer()->get(PlatformConfigurationHandler::class);
        if ($config->getParameter('registration.self')) {
            $defaultRole = $config->getParameter('registration.default_role');

            // allow anonymous registration only if the user is created with the default role
            if (0 === count(array_filter($user->getEntityRoles(), function (Role $role) use ($defaultRole) {
                // search for not allowed roles
                return Role::PLATFORM_ROLE === $role->getType() && $defaultRole !== $role->getName();
            }))) {
                return VoterInterface::ACCESS_GRANTED;
            }
            // TODO : this should also check the workspace roles come from public/registerable WS.
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return User::class;
    }

    /**
     * @return array
     */
    public function getSupportedActions()
    {
        return [self::CREATE, self::EDIT, self::DELETE, self::PATCH, self::VIEW];
    }
}
