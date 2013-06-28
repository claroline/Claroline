<?php

namespace Claroline\CoreBundle\Converter;

class ConfigurationException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct("@ParamConverter annotation error : {$message}");
    }
}