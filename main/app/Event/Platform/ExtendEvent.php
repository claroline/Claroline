<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Platform;

class ExtendEvent extends EnableEvent
{
    /** @var \DateTime */
    private $end;

    public function __construct(\DateTime $end)
    {
        $this->end = $end;
    }

    public function setEnd(\DateTime $end)
    {
        $this->end = $end;
    }

    public function getEnd()
    {
        return $this->end;
    }
}
