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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractToolEvent extends Event
{
    /** @var string */
    private $toolName;
    /** @var string */
    private $context;
    /** @var Workspace */
    private $workspace;

    public function __construct(string $toolName, string $context, ?Workspace $workspace = null)
    {
        $this->toolName = $toolName;
        $this->context = $context;
        $this->workspace = $workspace;
    }

    public function getToolName(): string
    {
        return $this->toolName;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }
}
