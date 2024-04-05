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

    public function getAvailableTools(?ContextSubjectInterface $contextSubject): array
    {
        return $this->toolProvider->getAvailableTools(static::getName(), $contextSubject);
    }

    public function getTools(?ContextSubjectInterface $contextSubject): array
    {
        return $this->toolProvider->getEnabledTools(static::getName(), $contextSubject);
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }
}
