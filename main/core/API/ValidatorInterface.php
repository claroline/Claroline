<?php

namespace Claroline\CoreBundle\API;

interface ValidatorInterface
{
    public function getClass();

    /**
     * Validates data sent to the API.
     * (usually the result of `json_decode` of the Request content).
     *
     * @param mixed $data - the data to validate
     *
     * @return array - the list of found errors (should used prop names as keys)
     */
    public function validate($data);
}
