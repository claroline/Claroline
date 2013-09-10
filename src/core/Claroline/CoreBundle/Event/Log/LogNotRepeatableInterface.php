<?php

namespace Claroline\CoreBundle\Event\Log;

interface LogNotRepeatableInterface
{
    public function getLogSignature();
}
