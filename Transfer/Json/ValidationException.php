<?php

namespace UJM\ExoBundle\Transfer\Json;

/**
 * Exception thrown by the ApiManager when a question or quiz
 * being imported doesn't pass validation.
 */
class ValidationException extends \Exception
{
    private $errors;

    public function __construct($message, array $errors)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
