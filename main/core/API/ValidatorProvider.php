<?php

namespace Claroline\CoreBundle\API;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.validator")
 */
class ValidatorProvider
{
    /**
     * The list of registered validators in the platform.
     *
     * @var array
     */
    private $validators = [];

    /**
     * Registers a new validator.
     *
     * @param ValidatorInterface $validator
     */
    public function add(ValidatorInterface $validator)
    {
        $this->validators[$validator->getClass()] = $validator;
    }

    /**
     * Gets a registered validator instance.
     *
     * @param string $class
     *
     * @return ValidatorInterface
     *
     * @throws \Exception
     */
    public function get($class)
    {
        if (empty($this->validators[$class])) {
            throw new \Exception(
                sprintf('No validator found for class "%s" Maybe you forgot to add the "claroline.validator" tag to your validator.', $class)
            );
        }

        return $this->validators[$class];
    }

    /**
     * Validates `data` using the `class` validator.
     *
     * @param string $class          - the class of the validator to use
     * @param mixed  $data           - the data to validate
     * @param bool   $throwException - if true an InvalidDataException is thrown instead of returning the errors
     *
     * @return array
     *
     * @throws InvalidDataException
     */
    public function validate($class, $data, $throwException = false)
    {
        $errors = $this->get($class)->validate($data);
        if (!empty($errors) && $throwException) {
            throw new InvalidDataException(
                sprintf('Invalid data for "%s".', $class),
                $errors
            );
        }

        return $errors;
    }
}
