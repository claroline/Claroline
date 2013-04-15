<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceCreateEvent extends LogGenericEvent
{
    const action = 'workspace_create';

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