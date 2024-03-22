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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoter extends AbstractRoleSubjectVoter
{
    public function __construct(
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    /**
     * @param User $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::VIEW:
            case self::OPEN:
                return $this->checkOpen($token, $object);
            case self::CREATE:
                return $this->checkCreate($token, $object);
            case self::ADMINISTRATE:
                return $this->checkAdministrate();
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
        // the user can open himself.
        if ($token->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isToolGranted('ADMINISTRATE', 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isToolGranted('OPEN', 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(TokenInterface $token, User $user): int
    {
        // the user can edit himself too.
        if ($token->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isToolGranted('ADMINISTRATE', 'community')) {
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

    private function checkAdministrate(): int
    {
        if ($this->isToolGranted('ADMINISTRATE', 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkDelete(TokenInterface $token, User $user): int
    {
        if ($this->isToolGranted('ADMINISTRATE', 'community')) {
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

    private function checkCreate(TokenInterface $token, User $user): int
    {
        // allow creation to administrators
        if ($this->isToolGranted('REGISTER', 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->isToolGranted('ADMINISTRATE', 'community')) {
            return VoterInterface::ACCESS_GRANTED;
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

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE, self::PATCH, self::VIEW];
    }
}
