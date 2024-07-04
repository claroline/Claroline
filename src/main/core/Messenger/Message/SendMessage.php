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

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

class SendMessage implements AsyncHighMessageInterface
{
    public function __construct(
        private readonly string $content,
        private readonly string $object,
        private readonly array $receiverIds,
        private readonly ?int $senderId = null
    ) {
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
