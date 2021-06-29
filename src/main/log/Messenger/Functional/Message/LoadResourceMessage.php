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

class LoadResourceMessage implements FunctionalMessageInterface
{
    public const EVENT_NAME = 'resource_load';

    private $message;
    private $resourceId;
    private $userId;

    public function __construct(
        string $message,
        int $resourceId,
        int $userId
    ) {
        $this->message = $message;
        $this->resourceId = $resourceId;
        $this->userId = $userId;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
