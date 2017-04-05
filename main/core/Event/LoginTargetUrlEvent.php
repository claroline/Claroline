<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/3/17
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class LoginTargetUrlEvent extends Event
{
    private $targets;

    public function __construct()
    {
        $this->targets = [];
    }

    public function addTarget($name, $path)
    {
        $this->targets[$name] = $path;
    }

    public function getTargets()
    {
        return $this->targets;
    }
}
