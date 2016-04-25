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

class LogWorkspaceEnterEvent extends LogGenericEvent implements LogNotRepeatableInterface
{
    const ACTION = 'workspace-enter';

    /**
     * Constructor.
     */
    public function __construct($workspace)
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
            null,
            true
        );
    }

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->workspace->getId();
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return;
    }
}
