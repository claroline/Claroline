<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\Component\Context\ContextComponent;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The Public context is an optional context (must be enabled by an admin) for anonymous users.
 */
final class PublicContext extends ContextComponent
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

    public static function getSubjectClass(): null
    {
        return null;
    }

    public function getSubject(?string $contextId): ?ContextSubjectInterface
    {
        return null;
    }

    public function isAvailable(): bool
    {
        return 'tool' === $this->config->getParameter('home.type');
    }

    public function getAccessErrors(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }

    public function isImpersonated(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool
    {
        return $this->securityManager->isImpersonated();
    }

    public function isGranted(string $permission, ?ContextSubjectInterface $contextSubject): bool
    {
        if ('ADMINISTRATE' === strtoupper($permission)) {
            return $this->securityManager->isAdmin();
        }

        return true;
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
                'name' => $this->config->getParameter('name'),
                'permissions' => [
                    'open' => true,
                    'administrate' => $this->securityManager->isAdmin(),
                ],
                'opening' => [
                    'type' => 'tool' === $type ? 'tool' : null,
                    'target' => 'home',
                ],
            ],
        ];
    }

    public function getOrganizations(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return $this->om->getRepository(Organization::class)->findBy([
            'public' => true,
        ]);
    }
}
