<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\AbstractContext;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DesktopContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om
    ) {
    }

    public static function getName(): string
    {
        return 'desktop';
    }

    public static function getIcon(): string
    {
        return 'atlas';
    }

    public function getObject(?string $contextId): ?ContextSubjectInterface
    {
        return null;
    }

    public function isAvailable(): bool
    {
        return $this->om->getRepository(OrderedTool::class)->countByDesktopAndRoles($this->tokenStorage->getToken()->getRoleNames());
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
}
