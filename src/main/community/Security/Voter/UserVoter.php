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

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\Voter\AbstractRoleSubjectVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoter extends AbstractRoleSubjectVoter
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    /**
     * @param User $object
     *
     * @return int
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::OPEN:
                return $this->checkOpen($token, $object);
            case self::CREATE:
                return $this->checkCreate($token, $object);
            case self::VIEW:
                return $this->checkView($token, $object);
            case self::ADMINISTRATE:
                return $this->checkAdministrate($token, $object);
            case self::EDIT:
                return $this->checkEdit($token, $object);
            case self::DELETE:
                return $this->checkDelete($token, $object);
            case self::PATCH:
                return $this->checkPatch($token, $object, $collection);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkOpen(TokenInterface $token, User $user): int
    {
        if ($this->isOrganizationManager($token, $user)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // the user can open himself too.
        if ($token->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->hasAdminToolAccess($token, 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // allow open for all of those who have the open right on community tool
        // TODO : this should also check user is in the same organization
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);
        /** @var OrderedTool $communityTools */
        $communityTool = $orderedToolRepo->findOneByNameAndDesktop('community');
        if ($communityTool && $this->isGranted('OPEN', $communityTool)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(TokenInterface $token, User $user): int
    {
        if ($this->isOrganizationManager($token, $user)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // the user can edit himself too.
        if ($token->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // allow defined roles
        $allowedRoles = $this->config->getParameter('profile.roles_edition');
        if (!empty($allowedRoles)) {
            foreach ($token->getRoleNames() as $role) {
                if (in_array($role, $allowedRoles)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkAdministrate(TokenInterface $token, User $user): int
    {
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
        if ($this->isOrganizationManager($token, $user)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // allow self unregistration
        if ($this->config->getParameter('registration.selfUnregistration') && $token->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkPatch(TokenInterface $token, User $user, ObjectCollection $collection = null): int
    {
        if (!$collection) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($collection->isInstanceOf(Role::class)) {
            return $this->checkPatchRoles($token, $user, $collection);
        }

        return $this->checkEdit($token, $user);
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
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);
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
        if ($this->config->getParameter('registration.self')) {
            $defaultRole = $this->config->getParameter('registration.default_role');

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

    public function getClass(): string
    {
        return User::class;
    }

    /**
     * @return array
     */
    public function getSupportedActions()
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE, self::PATCH, self::VIEW];
    }
}
