<?php

namespace UJM\ExoBundle\Library\Item\Definition\Exception;

class UnregisteredDefinitionException extends \Exception
{
    const TARGET_MIME_TYPE = 'mime';

    public function __construct($type, $target)
    {
        parent::__construct(sprintf(
            'No registered question handler for %s "%s"',
            $target,
            $type
        ));
    }
}
