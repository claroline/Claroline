<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PublicContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    public static function getShortName(): string
    {
        return 'public';
    }

    public function getObject(?string $contextId): mixed
    {
        return null;
    }

    public function isAvailable(?string $contextId, TokenInterface $token): bool
    {
        return 'tool' === $this->config->getParameter('home.type');
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
        $type = $this->config->getParameter('home.type');

        return [
            'data' => [
                'opening' => [
                    'type' => 'tool' === $type ? 'tool' : null,
                    'target' => 'home',
                    'menu' => $this->config->getParameter('home.menu'),
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
