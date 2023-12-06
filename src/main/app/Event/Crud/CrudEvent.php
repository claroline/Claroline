<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Crud;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Crud event class.
 */
class CrudEvent extends Event
{
    private $object;

    /**
     * @var bool
     */
    private $block = false;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param mixed $object  - The object created
     * @param array $options - An array of options
     */
    public function __construct($object, array $options = [])
    {
        $this->object = $object;
        $this->options = $options;
    }

    public function getClass(): string
    {
        // FIXME : this can return a Doctrine Proxy
        return get_class($this->object);
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function block()
    {
        $this->block = true;
    }

    public function allow()
    {
        $this->block = false;
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return !$this->block;
    }
}
