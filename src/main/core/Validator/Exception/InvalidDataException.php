<?php

namespace Claroline\CoreBundle\Validator\Exception;

/**
 * Exception thrown by the Api when invalid data are received.
 */
class InvalidDataException extends \Exception
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        $this->errors = $errors;

        $errorMessages = array_map(function ($error) {
            return sprintf(
                '  { path: %s, msg: %s }',
                $error['path'],
                $error['message']
            );
        }, $errors);

        if ($errorMessages) {
            $message = sprintf(
                "%s\n{\n%s\n}",
                $message,
                implode(",\n", $errorMessages)
            );
        }

        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
