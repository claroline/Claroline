<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class SecurityEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent")
     */
    public const AUTHENTICATION_FAILURE = 'event.security.authentication_failure';

    /**
     * @Event("Symfony\Component\Security\Http\Event\SwitchUserEvent")
     */
    public const SWITCH_USER = 'security.switch_user';
}
