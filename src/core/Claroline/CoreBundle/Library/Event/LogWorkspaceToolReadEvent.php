<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceToolReadEvent extends LogGenericEvent implements NotRepeatableLog
{
    const ACTION = 'ws_tool_read';

    /**
     * Constructor.
     */
    public function __construct($workspace, $toolName)
    {
        parent::__construct(
            self::ACTION,
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
        return $this->workspace->getId().'_'.$this->toolName;
    }
}