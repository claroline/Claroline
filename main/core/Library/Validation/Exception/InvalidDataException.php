<?php

namespace Claroline\CoreBundle\Library\Validation\Exception;

/**
 * Exception thrown by the Api when invalid data are received.
 */
class InvalidDataException extends \Exception
{
    private $errors;

    public function __construct($message, array $errors)
    {
        $this->errors = $errors;

        $errorMessages = array_map(function ($error) {
            return sprintf(
                '  { path: %s, msg: %s }',
                $error['path'],
                $error['message']
            );
        }, $errors);

        $message = sprintf(
            "%s\n{\n%s\n}",
            $message,
            implode(",\n", $errorMessages)
        );

        parent::__construct($message);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
