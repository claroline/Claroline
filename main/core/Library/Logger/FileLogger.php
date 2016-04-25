<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class FileLogger extends Logger
{
    public static function get($logFile, $name = 'default.claroline.logger')
    {
        $fileLogger = new self($name);
        $fileLogger->pushHandler(new StreamHandler($logFile));

        return $fileLogger;
    }

    //make the interface happy altough we don't use it -,-.
    //that way we can use the same parmaeter order than the sf2 one
    public function log($level, $log, array $context = array())
    {
        parent::log($level, $log, $context);
    }
}
