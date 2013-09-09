<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceDeleteEvent extends LogGenericEvent
{
    const ACTION = 'workspace-delete';

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

        $this->setIsDisplayedInAdmin(true);
    }
}
