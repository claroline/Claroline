<?php

namespace UJM\ExoBundle\Transfer\Json;

/**
 * Exception thrown when a JSON question handler cannot be registered
 * by the handler collector.
 */
class UnregisterableHandlerException extends \Exception
{
    const DUPLICATE_MIME = 0;
    const DUPLICATE_INTERACTION = 1;
    const DUPLICATE_SCHEMA = 2;
    const NOT_A_STRING_MIME = 3;
    const NOT_A_STRING_INTERACTION = 4;
    const NOT_A_STRING_SCHEMA = 5;

    public static function notAStringMimeType(QuestionHandlerInterface $handler)
    {
        return self::notAString($handler, 'MIME type', self::NOT_A_STRING_MIME);
    }

    public static function notAStringInteractionType(QuestionHandlerInterface $handler)
    {
        return self::notAString($handler, 'interaction type', self::NOT_A_STRING_INTERACTION);
    }

    public static function notAStringSchemaUri(QuestionHandlerInterface $handler)
    {
        return self::notAString($handler, 'JSON schema URI', self::NOT_A_STRING_SCHEMA);
    }

    public static function duplicateMimeType(QuestionHandlerInterface $handler)
    {
        return self::duplicate($handler, 'MIME type', $handler->getQuestionMimeType(), self::DUPLICATE_MIME);
    }

    public static function duplicateInteractionType(QuestionHandlerInterface $handler)
    {
        return self::duplicate($handler, 'interaction type', $handler->getInteractionType(), self::DUPLICATE_INTERACTION);
    }

    public static function duplicateSchemaUri(QuestionHandlerInterface $handler)
    {
        return self::duplicate($handler, 'JSON schema URI', $handler->getJsonSchemaUri(), self::DUPLICATE_SCHEMA);
    }

    private static function notAString(QuestionHandlerInterface $handler, $type, $error)
    {
        return new self(
            sprintf(
                'Cannot register JSON question handler %s: %s is not a string',
                get_class($handler),
                $type
            ),
            $error
        );
    }

    private static function duplicate(QuestionHandlerInterface $handler, $type, $value, $error)
    {
        return new self(
            sprintf(
                'Cannot register JSON question handler %s: a handler is already registered for %s "%s"',
                get_class($handler),
                $type,
                $value
            ),
            $error
        );
    }
}
