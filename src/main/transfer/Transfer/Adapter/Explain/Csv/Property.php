<?php

namespace Claroline\TransferBundle\Transfer\Adapter\Explain\Csv;

class Property
{
    public $name;
    public $type;
    public $description;
    public $required;
    public $isArray;

    public function __construct(
        $name,
        $type,
        $description,
        $required,
        $isArray = false
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->isArray = $isArray;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isArray()
    {
        return $this->isArray;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
