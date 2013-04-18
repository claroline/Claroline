<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceDeleteEvent extends LogGenericEvent
{
    const ACTION = 'workspace_delete';

    /**
     * Constructor.
     */
    public function __construct($workspace)
    {
        parent::__construct(
            self::ACTION,
            array(
                'workspace' => array(
                    'name' => $workspace->getName()
                ),
            ),
            null,
            null,
            null,
            null,
            $workspace
        );
    }
}