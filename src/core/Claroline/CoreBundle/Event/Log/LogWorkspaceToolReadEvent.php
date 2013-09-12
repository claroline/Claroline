<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceToolReadEvent extends LogGenericEvent implements LogNotRepeatableInterface
{
    const ACTION = 'workspace-tool-read';

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
        return self::ACTION.'_'.$this->workspace->getId().'_'.$this->toolName;
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
