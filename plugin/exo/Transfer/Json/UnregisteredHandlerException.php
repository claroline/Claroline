<?php

namespace UJM\ExoBundle\Transfer\Json;

class UnregisteredHandlerException extends \Exception
{
    const TARGET_MIME_TYPE = 'mime';
    const TARGET_INTERACTION = 'interaction';

    public function __construct($type, $target)
    {
        parent::__construct(sprintf(
            'No registered question handler for %s "%s"',
            $target,
            $type
        ));
    }
}
