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

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractContextEvent extends Event
{
    public function __construct(
        private readonly string $contextType,
        private readonly ?ContextSubjectInterface $contextSubject = null
    ) {
    }

    public function getContextType(): string
    {
        return $this->contextType;
    }

    public function getContextSubject(): ?ContextSubjectInterface
    {
        return $this->contextSubject;
    }
}
