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

    public function isAvailable(?string $contextId): bool;

    public function isManager(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool;

    public function isImpersonated(TokenInterface $token, ?ContextSubjectInterface $contextSubject): bool;

    public function getRoles(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array;

    public function getAccessErrors(TokenInterface $token, ?ContextSubjectInterface $contextSubject): array;

    /**
     * Get additional data required by the context (ex. current user evaluation).
     */
    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array;

    /**
     * Gets the list of tools enabled for the context.
     */
    public function getTools(?ContextSubjectInterface $contextSubject): array;

    /**
     * Gets the list of shortcuts for the context.
     */
    public function getShortcuts(?ContextSubjectInterface $contextSubject): array;
}
