<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Component\ComponentInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * ContextInterface is the interface implemented by all claroline contexts.
 */
interface ContextInterface extends ComponentInterface
{
    public static function getIcon(): string;

    public static function getSubjectClass(): ?string;

    public function getSubject(?string $contextId): ?ContextSubjectInterface;

    public function isAvailable(): bool;

    public function isGranted(string $permission, ?ContextSubjectInterface $contextSubject): bool;

    public function isImpersonated(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool;

    /**
     * Gets roles owned by the authenticated user in the current context.
     */
    public function getRoles(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array;

    public function getOrganizations(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): array;

    /**
     * Explain why the current user cannot open the context subject.
     */
    public function getAccessError(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): ?array;

    /**
     * Get additional data required by the context (ex. current user evaluation).
     */
    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array;

    public function create(?ContextSubjectInterface $contextSubject, array $data): void;

    public function update(?ContextSubjectInterface $contextSubject, array $data): void;
}
