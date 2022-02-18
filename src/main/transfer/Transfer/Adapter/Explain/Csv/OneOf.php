<?php

namespace Claroline\TransferBundle\Transfer\Adapter\Explain\Csv;

class OneOf
{
    public $oneOf;
    public $description;
    public $required;

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
