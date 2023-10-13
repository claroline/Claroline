<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DesktopContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    public static function getShortName(): string
    {
        return 'desktop';
    }

    public function getObject(?string $contextId): mixed
    {
        return null;
    }

    public function isAvailable(?string $contextId, TokenInterface $token): bool
    {
        // do user have access to at least one tool ?
        return !empty($this->toolManager->getOrderedToolsByDesktop($token->getRoleNames()));
    }

    public function getAccessErrors(?string $contextId, TokenInterface $token): array
    {
        return [

        ];
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
        $defaultTool = $this->config->getParameter('desktop.default_tool');

        return [
            'data' => [
                'opening' => [
                    'type' => $defaultTool ? 'tool' : null,
                    'target' => $defaultTool,
                    'menu' => $this->config->getParameter('desktop.menu'),
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
        // return $this->config->getParameter('desktop.shortcuts') ?? [];
        return [];
    }
}
