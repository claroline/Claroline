<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Component\Tool\ToolProvider;

abstract class AbstractContext implements ContextInterface
{
    protected readonly ToolProvider $toolProvider;

    public function setToolProvider(ToolProvider $toolProvider): void
    {
        $this->toolProvider = $toolProvider;
    }

    public function getTools(?ContextSubjectInterface $contextSubject): array
    {
        return $this->toolProvider->getEnabledTools(static::getName(), $contextSubject);
    }

    public function getShortcuts(?ContextSubjectInterface $contextSubject): array
    {
        // only supported by Workspace context atm
        return [];
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }
}
