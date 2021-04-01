<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Functional;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class ToolOpenEvent extends Event
{
    private $user;
    private $context;
    private $toolName;
    private $workspace;

    public function __construct(User $user, string $context, string $toolName, ?Workspace $workspace = null)
    {
        $this->user = $user;
        $this->context = $context;
        $this->toolName = $toolName;
        $this->workspace = $workspace;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getToolName(): string
    {
        return $this->toolName;
    }

    public function getWorkSpace(): ?Workspace
    {
        return $this->workspace;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans('toolOpen', ['userName' => $this->user->getUsername(), 'context' => $this->context, 'toolName' => $this->toolName], 'functional');
    }
}
