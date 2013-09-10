<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceUpdateEvent extends LogGenericEvent
{
    const ACTION = 'workspace-update';

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

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
