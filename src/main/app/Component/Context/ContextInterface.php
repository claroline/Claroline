<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Component\ComponentInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * ContextInterface is the interface implemented by all claroline contexts.
 */
interface ContextInterface extends ComponentInterface
{
    public function getObject(?string $contextId): mixed;

    public function isAvailable(?string $contextId, TokenInterface $token): bool;

    public function isManager(?string $contextId, TokenInterface $token): bool;

    public function isImpersonated(?string $contextId, TokenInterface $token): bool;

    public function getRoles(?string $contextId, TokenInterface $token): array;

    public function getAccessErrors(?string $contextId, TokenInterface $token): array;

    /**
     * Get additional data required by the context (ex. current user evaluation).
     */
    public function getAdditionalData(?string $contextId): array;

    /**
     * Gets the list of tools enabled for the context.
     */
    public function getTools(?string $contextId): array;

    /**
     * Gets the list of shortcuts for the context.
     */
    public function getShortcuts(?string $contextId): array;
}
