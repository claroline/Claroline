<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Context;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractContextEvent extends Event
{
    private string $contextType;
    private ?string $contextId;

    public function __construct(string $contextType, ?string $contextId)
    {
        $this->contextType = $contextType;
        $this->contextId = $contextId;
    }

    public function getContextType(): string
    {
        return $this->contextType;
    }

    public function getContextId(): string
    {
        return $this->contextId;
    }
}
