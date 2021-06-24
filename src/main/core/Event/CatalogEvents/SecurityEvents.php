<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class SecurityEvents
{
    /**
     * @Event("Symfony\Component\Security\Http\Event\SwitchUserEvent")
     */
    public const SWITCH_USER = 'security.switch_user';
}
