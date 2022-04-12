<?php

namespace Claroline\AuthenticationBundle\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class AuthenticationStamp implements StampInterface
{
    /** @var int */
    private $userId;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}
