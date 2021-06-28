<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class WorkspaceEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent")
     */
    public const OPEN = 'workspace.open';

    /**
     * @Event("Claroline\CoreBundle\Event\Workspace\CloseWorkspaceEvent")
     */
    public const CLOSE = 'workspace.close';
}
