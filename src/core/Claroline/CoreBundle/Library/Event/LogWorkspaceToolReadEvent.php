<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceToolReadEvent extends LogGenericEvent implements NotRepeatableLog
{
    const action = 'ws_tool_read';

    /**
     * Constructor.
     */
    public function __construct($workspace, $toolName)
    {
        parent::__construct(
            self::action,
            array(
                'workspace' => array(
                    'name' => $workspace->getName()
                )
            ),
            null,
            null,
            null,
            null,
            $workspace,
            null,
            $toolName
        );
    }

    public function getLogSignature()
    {
        return $this->getWorkspace()->getId().'_'.$this->getToolName();
    }
}