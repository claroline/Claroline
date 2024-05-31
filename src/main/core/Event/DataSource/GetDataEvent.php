<?php

namespace Claroline\CoreBundle\Event\DataSource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event which is fired when a DataSource is requested.
 *
 * The DataSource MUST populate the event and can be configured with an `options` array.
 */
class GetDataEvent extends Event
{
    /**
     * The data returned by the source.
     */
    private mixed $data = null;

    public function __construct(
        private readonly string $context,
        private readonly array $options = [],
        private readonly ?User $user = null,
        private readonly ?Workspace $workspace = null
    ) {
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    /**
     * Get the current options of the DataSource.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the data provided by the DataSource.
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Set the event data.
     */
    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
