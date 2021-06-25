<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LogBundle\Messenger\Security\Message;

class AuthenticationFailureMessage implements SecurityMessageInterface
{
    public const EVENT_NAME = 'event.security.authentication_failure';

    private $targetId;
    private $doerId;
    private $message;

    public function __construct(
        ?int $targetId,
        ?int $doerId,
        string $message
    ) {
        $this->targetId = $targetId;
        $this->doerId = $doerId;
        $this->message = $message;
    }

    public function getTargetId(): ?int
    {
        return $this->targetId;
    }

    public function getDoerId(): ?int
    {
        return $this->doerId;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
