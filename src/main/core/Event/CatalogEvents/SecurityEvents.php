<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class SecurityEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Log\UserLoginEvent")
     */
    public const USER_LOGIN = 'event.security.user_login';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\UserLogoutEvent")
     */
    public const USER_LOGOUT = 'event.security.user_logout';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\UserEnableEvent")
     */
    public const USER_ENABLE = 'event.security.user_enable';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\UserDisableEvent")
     */
    public const USER_DISABLE = 'event.security.user_disable';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\NewPasswordEvent")
     */
    public const NEW_PASSWORD = 'event.security.new_password';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\ForgotPasswordEvent")
     */
    public const FORGOT_PASSWORD = 'event.security.forgot_password';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\AddRoleEvent")
     */
    public const ADD_ROLE = 'event.security.add_role';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\RemoveRoleEvent")
     */
    public const REMOVE_ROLE = 'event.security.remove_role';

    /**
     * @Event("Claroline\CoreBundle\Event\Log\ViewAsEvent")
     */
    public const VIEW_AS = 'event.security.view_as';
}
