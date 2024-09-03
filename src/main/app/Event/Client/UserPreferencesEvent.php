<?php

namespace Claroline\AppBundle\Event\Client;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event dispatched when the application UI is rendered
 * giving the chance to plugins to inject some custom user preferences which will be available in the javascript client.
 */
class UserPreferencesEvent extends Event
{
    private array $preferences = [];

    public function __construct(
        private readonly ?User $user = null
    ) {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getPreferences(): array
    {
        return $this->preferences;
    }

    public function addPreferences(string $key, mixed $value): void
    {
        $this->preferences[$key] = $value;
    }
}
