<?php

namespace Claroline\CoreBundle\Library\Utilities;

class UnloadedExtensionException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}