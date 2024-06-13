<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
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

    public static function getName(): string
    {
        return 'public';
    }

    public static function getIcon(): string
    {
        return 'home';
    }

    public static function getOrder(): int
    {
        return 1;
    }

    public function getObject(?string $contextId): ?ContextSubjectInterface
    {
        return null;
    }

    public function isAvailable(): bool
    {
        return 'tool' === $this->config->getParameter('home.type')
            && (empty($this->securityManager->getCurrentUser()) || $this->securityManager->isAdmin());
    }

    public function isRoot(): bool
    {
        return true;
    }

    public function getAccessErrors(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }

    public function isImpersonated(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->securityManager->isImpersonated();
    }

    public function isManager(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->securityManager->isAdmin();
    }

    public function getRoles(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        $currentUser = $this->securityManager->getCurrentUser();
        if (empty($currentUser)) {
            return [];
        }

        return array_filter($currentUser->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM_ROLE === $role->getType();
        });
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        // for retro-compatibility, should not be exposed here
        $type = $this->config->getParameter('home.type');

        return [
            'data' => [
                'permissions' => [
                    'open' => true,
                    'administrate' => $this->securityManager->isAdmin(),
                ],
                'opening' => [
                    'type' => 'tool' === $type ? 'tool' : null,
                    'target' => 'home',
                    'menu' => $this->config->getParameter('home.menu'),
                ],
            ],
        ];
    }
}
