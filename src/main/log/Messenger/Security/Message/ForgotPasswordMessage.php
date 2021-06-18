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

class ForgotPasswordMessage
{
    private $targetId;
    private $doerId;
    private $eventName;

    public function __construct(int $targetId, int $doerId, string $eventName)
    {
        $this->targetId = $targetId;
        $this->doerId = $doerId;
        $this->eventName = $eventName;
    }

    public function getTargetId(): int
    {
        return $this->targetId;
    }

    public function getDoerId(): int
    {
        return $this->doerId;
    }

    public function getName(): string
    {
        return $this->eventName;
    }
}
