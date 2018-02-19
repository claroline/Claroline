<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Crud;

/**
 * Crud event class.
 */
class PatchEvent extends CrudEvent
{
    /** @var string */
    private $property;
    /** @var mixed */
    private $value;
    /** @var string */
    private $action;

    /**
     * @param mixed  $object   - The object created
     * @param array  $options  - An array of options
     * @param string $property - The property name
     * @param mixed  $value    - The property value
     * @param string $action   - The setter mode
     */
    public function __construct($object, array $options, $property, $value, $action)
    {
        parent::__construct($object, $options);

        $this->property = $property;
        $this->value = $value;
        $this->action = $action;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getMethodName()
    {
        return $this->action.ucfirst(strtolower($this->property));
    }
}
