<?php

namespace Claroline\AppBundle\Event;

/**
 * Events fired during the rendering of the Web client.
 */
final class ClientEvents
{
    /**
     * The CONFIGURE event occurs when the client configuration is retrieved.
     *
     * This event allows you to append custom parameters to the base configuration in order to
     * make them available in the Web client.
     *
     * @Event("Claroline\AppBundle\Event\Client\ConfigureEvent")
     */
    public const CONFIGURE = 'claroline_populate_client_config';

    /**
     * The USER_PREFERENCES event occurs when the preferences of the current user are retrieved.
     * It is fired for authenticated AND anonymous users.
     *
     * This event allows you to append custom preferences to the base preferences in order to
     * make them available in the Web client.
     *
     * @Event("Claroline\AppBundle\Event\Client\UserPreferencesEvent")
     */
    public const USER_PREFERENCES = 'client.user_preferences';

    /**
     * The JAVASCRIPTS event occurs when the client configuration is retrieved.
     *
     * This event allows you to append custom javascripts to web client.
     *
     * @Event("Claroline\AppBundle\Event\Client\InjectJavascriptEvent")
     */
    public const JAVASCRIPTS = 'layout.inject.javascript';

    /**
     * The JAVASCRIPTS event occurs when the client configuration is retrieved.
     *
     * This event allows you to append custom styles to web client.
     *
     * @Event("Claroline\AppBundle\Event\Client\InjectStylesheetEvent")
     */
    public const STYLESHEETS = 'layout.inject.stylesheet';
}
