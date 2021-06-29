<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\BadgeMessageInterface;

class RemoveBadgeMessage implements BadgeMessageInterface
{
    public const EVENT_NAME = 'badge_remove';

    private $message;
    private $userId;

    public function __construct(string $message, int $userId)
    {
        $this->message = $message;
        $this->userId = $userId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
