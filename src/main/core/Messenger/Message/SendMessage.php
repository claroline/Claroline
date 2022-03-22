<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class SendMessage implements AsyncMessageInterface
{
    /** @var string */
    private $content;
    /** @var string */
    private $object;
    /** @var array */
    private $receiverIds;
    /** @var int|null */
    private $senderId;

    public function __construct(string $content, string $object, array $receiverIds, ?int $senderId = null)
    {
        $this->content = $content;
        $this->object = $object;
        $this->receiverIds = $receiverIds;
        $this->senderId = $senderId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getReceiverIds(): array
    {
        return $this->receiverIds;
    }

    public function getSenderId(): ?int
    {
        return $this->senderId;
    }
}
