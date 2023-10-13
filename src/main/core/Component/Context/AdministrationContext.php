<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Administration context is attended to manage all the low-level application
 * parameters and features.
 * Therefore, only a limited number of users should have access to this part of the app.
 */
class AdministrationContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    public static function getShortName(): string
    {
        return 'administration';
    }

    public function getObject(?string $contextId): mixed
    {
        return null;
    }

    public function isAvailable(?string $contextId, TokenInterface $token): bool
    {
        return !empty($this->securityManager->getCurrentUser())
            && !empty($this->toolManager->getAdminToolsByRoles($token->getRoleNames()));
    }

    public function getAccessErrors(?string $contextId, TokenInterface $token): array
    {
        return [];
    }

    public function isImpersonated(?string $contextId, TokenInterface $token): bool
    {
        return $this->securityManager->isImpersonated();
    }

    public function isManager(?string $contextId, TokenInterface $token): bool
    {
        return $this->securityManager->isAdmin();
    }

    public function getAdditionalData(?string $contextId): array
    {
        // for retro-compatibility, should not be exposed here
        $defaultTool = $this->config->getParameter('admin.default_tool');

        return [
            'data' => [
                'opening' => [
                    'type' => $defaultTool ? 'tool' : null,
                    'target' => $defaultTool,
                    'menu' => $this->config->getParameter('admin.menu'),
                ],
            ],
        ];
    }

    public function getRoles(?string $contextId, TokenInterface $token): array
    {
        $currentUser = $this->securityManager->getCurrentUser();
        if (empty($currentUser)) {
            return [];
        }

        return array_filter($currentUser->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM_ROLE === $role->getType();
        });
    }

    public function getShortcuts(?string $contextId): array
    {
        // only supported by Workspace context atm
        return [];
    }
}
