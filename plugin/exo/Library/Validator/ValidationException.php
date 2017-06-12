<?php

namespace UJM\ExoBundle\Library\Validator;

/**
 * Exception thrown by the Api when invalid data are received.
 *
 * @todo : use \Claroline\CoreBundle\Library\Validation\Exception\InvalidDataException
 *
 * @deprecated
 */
class ValidationException extends \Exception
{
    private $errors;

    public function __construct($message, array $errors, array $data = [])
    {
        $this->errors = $errors;
        $message = "The json schema is invalid: \n";
        $message .= json_encode($data)."\n";
        $message .= "error: \n";

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
