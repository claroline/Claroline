<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
                    'name' => $workspace->getName(),
                ),
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
        return;
    }
}
