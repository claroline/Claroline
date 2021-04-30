<?php

namespace Claroline\AppBundle\API;

interface ValidatorInterface
{
    public static function getClass(): string;

    /**
     * Validates data sent to the API.
     * (usually the result of `json_decode` of the Request content).
     *
     * @param mixed $data - the data to validate
     * @param mixed $mode - the validation mode
     *
     * @return array - the list of found errors (should used prop names as keys)
     */
    public function validate($data, $mode, array $options = []);

    /**
     * A list of unique properties you want to check (they will be checked by
     * the provider so the validate method is easier)
     * with the format [$dataPropName => $entityPropName].
     *
     * @todo fetch default from json-schema
     *
     * @return array
     */
    public function getUniqueFields();
}
