<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Converter;

class InvalidConfigurationException extends \Exception
{
    const MISSING_NAME = 1;
    const MISSING_CLASS = 2;
    const MISSING_ID = 3;

    private static $messages = array(
        self::MISSING_NAME => 'the controller parameter name is mandatory',
        self::MISSING_CLASS => 'the "class" field is mandatory',
        self::MISSING_ID => 'the "id" option is mandatory',
    );

    public function __construct($code)
    {
        if (!isset(self::$messages[$code])) {
            throw new \InvalidArgumentException(
                'The code parameter must be a MISSING_* class constant'
            );
        }

        $message = self::$messages[$code];
        parent::__construct("@ParamConverter configuration error : {$message}", $code);
    }
}
