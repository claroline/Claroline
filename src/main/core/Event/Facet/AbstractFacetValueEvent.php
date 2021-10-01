<?php

namespace Claroline\CoreBundle\Event\Facet;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractFacetValueEvent extends Event
{
    /**
     * The parent object which holds the facets feature (eg. User, ClacoForm Entry).
     *
     * @var mixed
     */
    private $object;

    /** @var string */
    private $fieldType;
    private $value;
    private $formattedValue;

    public function __construct($object, string $fieldType, $value)
    {
        $this->object = $object;
        $this->fieldType = $fieldType;
        $this->value = $value;
        $this->formattedValue = $value;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFormattedValue()
    {
        return $this->formattedValue;
    }

    public function setFormattedValue($formattedValue)
    {
        $this->formattedValue = $formattedValue;
    }
}
