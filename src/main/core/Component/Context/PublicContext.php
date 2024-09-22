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
 * The Public context is an optional context (must be enabled by an admin) for anonymous users.
 */
class PublicContext extends AbstractContext
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config,
        private readonly ObjectManager $om
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

    public function getObject(?string $contextId): ?ContextSubjectInterface
    {
        return null;
    }

    public function isAvailable(): bool
    {
        return 'tool' === $this->config->getParameter('home.type')
            && (empty($this->securityManager->getCurrentUser()) || $this->securityManager->isAdmin());
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

    public function getRoles(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        $anonymousRole = $this->om->getRepository(Role::class)->findOneBy(['name' => PlatformRoles::ANONYMOUS]);

        if ($anonymousRole) {
            return [$anonymousRole];
        }

        return [];
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
