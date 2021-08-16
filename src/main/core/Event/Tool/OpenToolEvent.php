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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class OpenToolEvent extends Event
{
    private $workspace;
    private $user;
    private $context;
    private $toolName;

    /** @var array */
    private $data = [];

    public function __construct(
        ?Workspace $workspace = null,
        ?User $user = null,
        ?string $context = null,
        ?string $toolName = null
    ) {
        $this->workspace = $workspace;
        $this->user = $user;
        $this->context = $context;
        $this->toolName = $toolName;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getUser(): ?User
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

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans('toolOpen', ['userName' => $this->user->getUsername(), 'context' => $this->context, 'toolName' => $this->toolName], 'tools');
    }
}
