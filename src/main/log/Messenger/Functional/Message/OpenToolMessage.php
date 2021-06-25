<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LogBundle\Messenger\Functional\Message;

class OpenToolMessage  implements FunctionalMessageInterface
{
    public const EVENT_NAME = 'tool_open';

    private $message;
    private $user;
    private $workspace;

    public function __construct(
        string $message,
        int $user,
        ?int $workspace = null
    ) {
        $this->message = $message;
        $this->user = $user;
        $this->workspace = $workspace;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function getUser(): int
    {
        return $this->user;
    }

    public function getWorkspace(): ?int
    {
        return $this->workspace;
    }
}
