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

class RefreshCacheEvent extends Event
{
    private $parameters;

    public function __construct()
    {
        $this->parameters = array();
    }

    public function addCacheParameter($key, $value)
    {
        $this->parameters = array_merge($this->parameters, array($key => $value));
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
