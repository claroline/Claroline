<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Component\ComponentInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * ContextInterface is the interface implemented by all claroline contexts.
 */
interface ContextInterface extends ComponentInterface
{
    public function getObject(?string $contextId): ?ContextSubjectInterface;

    public static function getIcon(): string;

    /**
     * @deprecated
     */
    public static function getOrder(): int;

    public function isAvailable(): bool;

    public function isRoot(): bool;

    public function isManager(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool;

    public function isImpersonated(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool;

    public function getRoles(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array;

    public function getAccessErrors(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array;

    /**
     * Get additional data required by the context (ex. current user evaluation).
     */
    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array;

    /**
     * Gets the list of tools available for the context.
     * It contains all the tools implemented by enabled plugins.
     */
    public function getAvailableTools(?ContextSubjectInterface $contextSubject): array;

    /**
     * Gets the list of tools enabled for the context.
     */
    public function getTools(?ContextSubjectInterface $contextSubject): array;
}
