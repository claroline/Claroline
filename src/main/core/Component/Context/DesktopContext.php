<?php

namespace Claroline\CoreBundle\Component\Context;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextComponent;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The Desktop context is the main context for all the authenticated users.
 */
final class DesktopContext extends ContextComponent
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly PlatformConfigurationHandler $config,
        private readonly SerializerProvider $serializer
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
        return true;
    }

    public function getAccessErrors(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return [
            'noRights' => true,
        ];
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

        return !$this->securityManager->isAnonymous();
    }

    public function getRoles(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        $currentUser = $this->securityManager->getCurrentUser();
        if (empty($currentUser)) {
            return [];
        }

        return array_filter($currentUser->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM === $role->getType();
        });
    }

    public function getOrganizations(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array
    {
        return $token->getUser()->getOrganizations();
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        // for retro-compatibility, should not be exposed here
        $defaultTool = $this->config->getParameter('desktop.default_tool');

        $mainOrganization = $this->securityManager->getCurrentUser()?->getMainOrganization();

        $serializedOrganization = $this->serializer->serialize($mainOrganization);
        unset($serializedOrganization['id']); // FIXME : for now some desktop tools will crash if they receive an id

        return [
            'data' => array_merge_recursive($serializedOrganization, [
                'permissions' => [
                    'open' => !$this->securityManager->isAnonymous(),
                    'administrate' => $this->securityManager->isAdmin(),
                ],
                'opening' => [
                    'type' => $defaultTool ? 'tool' : null,
                    'target' => $defaultTool,
                ],
            ]),
        ];
    }
}
