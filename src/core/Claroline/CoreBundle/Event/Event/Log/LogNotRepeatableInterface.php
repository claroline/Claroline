<?php

namespace Claroline\CoreBundle\Event\Event\Log;

interface LogNotRepeatableInterface
{
    function getLogSignature();
}