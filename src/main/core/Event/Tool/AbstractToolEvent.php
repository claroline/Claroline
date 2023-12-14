<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractToolEvent extends Event
{
    private string $toolName;
    private string $context;
    private ?ContextSubjectInterface $contextSubject = null;

    public function __construct(string $toolName, string $context, ContextSubjectInterface $contextSubject = null)
    {
        $this->toolName = $toolName;
        $this->context = $context;
        $this->contextSubject = $contextSubject;
    }

    public function getToolName(): string
    {
        return $this->toolName;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getContextSubject(): ?ContextSubjectInterface
    {
        return $this->contextSubject;
    }

    /**
     * @deprecated use getContextSubject() instead
     */
    public function getWorkspace(): ?ContextSubjectInterface
    {
        return $this->getContextSubject();
    }
}
