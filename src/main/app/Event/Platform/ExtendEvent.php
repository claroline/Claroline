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
    private \DateTimeInterface $end;

    public function __construct(\DateTimeInterface $end)
    {
        $this->end = $end;
    }

    public function setEnd(\DateTimeInterface $end): void
    {
        $this->end = $end;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }
}
