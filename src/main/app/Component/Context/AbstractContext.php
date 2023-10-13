<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\CoreBundle\Manager\Tool\ToolManager;

abstract class AbstractContext implements ContextInterface
{
    protected readonly ToolManager $toolManager;

    public function setToolManager(ToolManager $toolManager): void
    {
        $this->toolManager = $toolManager;
    }

    public function getTools(?string $contextId): array
    {
        return $this->toolManager->getOrderedTools(static::getShortName(), $contextId);
    }
}
