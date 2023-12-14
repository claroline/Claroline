<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class WorkspaceEvents
{
    /**
     * Event fired when a User try to open a Workspace he can not.
     * This allows plugin to integrate custom processes in order to grant him access if possible.
     *
     * For example, the Training plugin will display the linked courses and let the user register
     * to them to get access to the workspace.
     *
     * @Event("Claroline\CoreBundle\Event\Workspace\AccessRestrictedEvent")
     */
    public const ACCESS_RESTRICTED = 'workspace.access_restricted';
}
