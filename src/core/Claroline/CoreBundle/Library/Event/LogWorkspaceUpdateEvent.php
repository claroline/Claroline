<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceUpdateEvent extends LogGenericEvent
{
    const action = 'ws_update';

    /**
     * Constructor.
     */
    public function __construct($workspace)
    {
        parent::__construct(
            self::action,
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