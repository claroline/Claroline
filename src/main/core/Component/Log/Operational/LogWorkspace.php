<?php

namespace Claroline\CoreBundle\Component\Log\Operational;

use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogWorkspace extends AbstractOperationalLog
{
    public function __construct(
        private readonly RoutingHelper $routingHelper
    ) {
    }

    public static function getName(): string
    {
        return 'workspace';
    }

    protected static function getEntityClass(): string
    {
        return Workspace::class;
    }

    /** @param Workspace $object */
    protected function getContext(object $object): string
    {
        return WorkspaceContext::getName();
    }

    /** @param Workspace $object */
    protected function getContextId(object $object): ?string
    {
        return $object->getContextIdentifier();
    }

    /** @param Workspace $object */
    protected function getObjectPath(object $object): ?string
    {
        return $this->routingHelper->workspaceUrl($object);
    }
}
