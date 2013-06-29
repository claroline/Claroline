<?php

namespace Claroline\CoreBundle\Converter;

class InvalidConfigurationException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct("@ParamConverter annotation error : {$message}");
    }
}