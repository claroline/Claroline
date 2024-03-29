<?php

namespace Claroline\AppBundle\Component\Context;

/**
 * ContextualInterface is the interface implemented by claroline components
 * which are only available for certain contexts.
 */
interface ContextualInterface
{
    /**
     * Checks if the component supports a defined context type.
     */
    public function supportsContext(string $context): bool;

    public function supportsSubject(ContextSubjectInterface $subject): bool;
}
