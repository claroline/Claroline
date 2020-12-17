<?php

namespace Claroline\AppBundle\API\Transfer\Adapter\Explain\Csv;

class OneOf
{
    public function __construct(array $schemas, $description, $required)
    {
        $this->oneOf = $schemas;
        $this->description = $description;
        $this->required = $required;
    }

    public function getExplanations()
    {
        return $this->oneOf;
    }
}
