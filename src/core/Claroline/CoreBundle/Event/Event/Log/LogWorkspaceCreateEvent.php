<?php

namespace Claroline\CoreBundle\Event\Event\Log;

class LogWorkspaceCreateEvent extends LogGenericEvent
{
    const ACTION = 'workspace_create';

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
