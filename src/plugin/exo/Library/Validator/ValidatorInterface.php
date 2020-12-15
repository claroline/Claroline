<?php

namespace UJM\ExoBundle\Library\Validator;

interface ValidatorInterface
{
    public function validate($data, array $options = []);
}
