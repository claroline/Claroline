<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

class SubmitLogs implements AsyncHighMessageInterface
{
    public function __construct(
        private readonly string $type,
        private readonly string $doerIp,
        private readonly array $logs = []
    ) {
    }

    /**
     * Gets the type of logs being submitted.
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getDoerIp(): string
    {
        return $this->doerIp;
    }

    /**
     * Get the list of logs to create.
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
