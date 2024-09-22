<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The Administration context is attended to manage all the low-level application
 * parameters and features.
 * Therefore, only a limited number of users should have access to this part of the app.
 */
class AdministrationContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config,
        private readonly ObjectManager $om
    ) {
    }

    public static function getName(): string
    {
        return 'administration';
    }

    public static function getIcon(): string
    {
        return 'sliders';
    }

    public function getObject(?string $contextId): ?ContextSubjectInterface
    {
        return null;
    }

    public function isAvailable(): bool
    {
        return $this->securityManager->isAdmin();
    }

    public function getAccessErrors(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }

    public function isImpersonated(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->securityManager->isImpersonated();
    }

    public function isManager(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->securityManager->isAdmin();
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        // for retro-compatibility, should not be exposed here
        $defaultTool = $this->config->getParameter('admin.default_tool');

        return [
            'data' => [
                'permissions' => [
                    'open' => $this->securityManager->isAdmin(),
                    'administrate' => $this->securityManager->isAdmin(),
                ],
                'opening' => [
                    'type' => $defaultTool ? 'tool' : null,
                    'target' => $defaultTool,
                    'menu' => $this->config->getParameter('admin.menu'),
                ],
            ],
        ];
    }

    public function getRoles(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        $adminRole = $this->om->getRepository(Role::class)->findOneBy(['name' => PlatformRoles::ADMIN]);

        if ($adminRole) {
            return [$adminRole];
        }

        return [];
        /*$currentUser = $this->securityManager->getCurrentUser();
        if (empty($currentUser)) {
            return [];
        }

        return array_filter($currentUser->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM === $role->getType();
        });*/
    }
}
