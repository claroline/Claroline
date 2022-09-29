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

class LogAdminToolReadEvent extends LogGenericEvent implements LogNotRepeatableInterface
{
    const ACTION = 'admin-tool-read';

    /**
     * Constructor.
     */
    public function __construct($toolName)
    {
        parent::__construct(
            self::ACTION,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $toolName,
            null,
            null,
            true
        );
    }

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->toolName;
    }

    public static function getRestriction()
    {
        return [];
    }
}
