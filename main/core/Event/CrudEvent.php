<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CrudEvent extends Event
{
    private $object;
    private $block;

    public function __construct($object)
    {
        $this->object = $object;
        $this->block = false;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function block()
    {
        $this->block = true;
    }

    public function allow()
    {
        $this->block = false;
    }

    public function isAllowed()
    {
        return !$this->block;
    }
}
