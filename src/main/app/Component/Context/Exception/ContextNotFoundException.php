<?php

namespace Claroline\AppBundle\Component\Context\Exception;

class ContextNotFoundException extends \RuntimeException
{
    public function __construct(string $contextType, ?string $contextId = null, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Context of type "%s" not found for ID "%s"', $contextType, $contextId), 0, $previous);
    }
}
