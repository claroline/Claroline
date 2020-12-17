<?php

namespace UJM\ExoBundle\Library\Item\Definition\Exception;

use UJM\ExoBundle\Library\Item\Definition\ItemDefinitionInterface;

/**
 * Exception thrown when a JSON question handler cannot be registered
 * by the handler collector.
 */
class UnregisterableDefinitionException extends \Exception
{
    const DUPLICATE_MIME = 1;
    const NOT_A_STRING_MIME = 2;
    const UNSUPPORTED_MIME = 3;

    public static function notAStringMimeType(ItemDefinitionInterface $handler)
    {
        return self::notAString($handler, 'MIME type', self::NOT_A_STRING_MIME);
    }

    public static function unsupportedMimeType(ItemDefinitionInterface $handler)
    {
        return self::unsupported($handler, 'MIME type', self::UNSUPPORTED_MIME);
    }

    public static function duplicateMimeType(ItemDefinitionInterface $handler)
    {
        return self::duplicate($handler, 'MIME type', $handler->getMimeType(), self::DUPLICATE_MIME);
    }

    private static function notAString(ItemDefinitionInterface $handler, $type, $error)
    {
        return new self(
            sprintf(
                'Cannot register question handler %s: %s is not a string',
                get_class($handler),
                $type
            ),
            $error
        );
    }

    private static function unsupported(ItemDefinitionInterface $handler, $type, $error)
    {
        return new self(
            sprintf(
                'Cannot register question definition %s: %s is not supported',
                get_class($handler),
                $type
            ),
            $error
        );
    }

    private static function duplicate(ItemDefinitionInterface $handler, $type, $value, $error)
    {
        return new self(
            sprintf(
                'Cannot register question definition %s: a definition is already registered for %s "%s"',
                get_class($handler),
                $type,
                $value
            ),
            $error
        );
    }
}
