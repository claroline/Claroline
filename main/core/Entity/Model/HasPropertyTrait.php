<?php

namespace Claroline\CoreBundle\Entity\Model;

trait HasPropertyTrait
{
    public function propertyExists($property)
    {
        if (!property_exists($this, $property)) {
            $error = "Property {$property} does not exists in class ".get_class($this).'. This property is required if you want to patch it.';
            throw new \Exception($error);
        }
    }
}
