<?php

namespace Claroline\AuthenticationBundle\Component\OAuth;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the oauth2 integration defined in the Claroline app.
 *
 * A tool MUST:
 *   - be declared as a symfony service and tagged with "claroline.component.oauth2".
 *   - implement the ToolInterface interface (or the AbstractTool class in most cases).
 */
class OAuth2Provider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredOAuth
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.oauth';
    }

    /**
     * Get the list of all the oauth2 injected in the app by the current plugins.
     * It does not contain oauth2 for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredOAuth;
    }

    public function getAvailableOAuths(): array
    {
        return iterator_to_array($this->getRegisteredComponents());
    }
}
