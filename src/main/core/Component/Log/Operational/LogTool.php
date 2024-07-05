<?php

namespace Claroline\CoreBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogTool extends AbstractOperationalLog
{
    public function __construct(
        private readonly RoutingHelper $routingHelper
    ) {
    }

    public static function getName(): string
    {
        return 'tool';
    }

    protected static function getEntityClass(): string
    {
        return OrderedTool::class;
    }

    /** @param OrderedTool $object */
    protected function getObjectName(object $object): string
    {
        return $this->trans($object->getName(), [], 'tools');
    }

    /** @param OrderedTool $object */
    protected function getObjectPath(object $object): ?string
    {
        return $this->routingHelper->toolUrl($object->getName(), $object->getContextName(), $object->getContextId());
    }

    /** @param OrderedTool $object */
    protected function getContext(object $object): string
    {
        return $object->getContextName();
    }

    /** @param OrderedTool $object */
    protected function getContextId(object $object): ?string
    {
        return $object->getContextId();
    }
}
