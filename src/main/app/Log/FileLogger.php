<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class FileLogger extends Logger
{
    public static function get($logFile, $name = 'default.claroline.logger')
    {
        $fileLogger = new self($name);
        $fileLogger->pushHandler(new StreamHandler($logFile));

        return $fileLogger;
    }
}
