<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceCreateEvent extends LogGenericEvent
{
    const ACTION = 'workspace-create';

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

        $this->isDisplayedInAdmin(true);
    }
}
