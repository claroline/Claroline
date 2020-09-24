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
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

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

    private function checkEdit(TokenInterface $token, User $user): int
    {
        //the user can edit himself too.
        //He just can add roles and stuff and this should be checked later
        if ($token->getUser() === $user) {
            return true;
        }

        return $this->isOrganizationManager($token, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    private function checkView(TokenInterface $token, User $user): int
    {
        return $this->isOrganizationManager($token, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    private function checkDelete(TokenInterface $token, User $user): int
    {
        return $this->isOrganizationManager($token, $user) ?
            VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    private function checkPatch(TokenInterface $token, User $user, ObjectCollection $collection = null): int
    {
        //single property: no check now
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($collection->isInstanceOf('Claroline\CoreBundle\Entity\Role')) {
            // check if we can add a workspace (this block is mostly a c/c from RoleVoter)
            $nonAuthorized = array_filter($collection->toArray(), function (Role $role) use ($token) {
                $workspace = $role->getWorkspace();
                if ($workspace) {
                    if ($this->isGranted(['community', 'edit'], $workspace)) {
                        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
                        // If user is workspace manager then grant access
                        if ($workspaceManager->isManager($workspace, $token)) {
                            return false;
                        }

                        // Otherwise only allow modification of roles the current user owns
                        if (in_array($role->getName(), $token->getRoleNames())) {
                            return false;
                        }
                    }

                    // If public registration is enabled and user try to get the default role, grant access
                    if ($workspace->getSelfRegistration() && $workspace->getDefaultRole()) {
                        if ($workspace->getDefaultRole()->getId() === $role->getId()) {
                            return false;
                        }
                    }

                    // user has no community right on the workspace he cannot add anything
                    return true;
                }

                if (Role::PLATFORM_ROLE === $role->getType() && in_array($role->getName(), $token->getRoleNames())) {
                    return false;
                }

                return true;
            });

            if (0 < count($nonAuthorized)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_GRANTED;
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
        // allow creation to administrators
        if ($this->hasAdminToolAccess($token, 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isOrganizationManager($token, $user)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // allow creation for all of those who have the create right on a community tool
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->getObjectManager()->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        /** @var OrderedTool[] $communityTools */
        $communityTools = $orderedToolRepo->findByName('community');
        foreach ($communityTools as $communityTool) {
            // we do not take into account tool in personal ws, otherwise anyone will be granted
            // (users are managers of their personal ws)
            if ((empty($communityTool->getWorkspace()) || !$communityTool->getWorkspace()->isPersonal()) && $this->isGranted('CREATE_USER', $communityTool)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        // allow creation for self registration
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
