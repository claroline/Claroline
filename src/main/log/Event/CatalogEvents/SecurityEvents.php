<?php

namespace Claroline\LogBundle\Event\CatalogEvents;

final class SecurityEvents
{
    /**
     * @Event("Claroline\LogBundle\Event\Security\UserLoginEvent")
     */
    public const USER_LOGIN = 'event.security.user_login';

    /**
     * @Event("Claroline\LogBundle\Event\Security\UserLogoutEvent")
     */
    public const USER_LOGOUT = 'event.security.user_logout';

    /**
     * @Event("Claroline\LogBundle\Event\Security\UserEnableEvent")
     */
    public const USER_ENABLE = 'event.security.user_enable';

    /**
     * @Event("Claroline\LogBundle\Event\Security\UserDisableEvent")
     */
    public const USER_DISABLE = 'event.security.user_disable';

    /**
     * @Event("Claroline\LogBundle\Event\Security\NewPasswordEvent")
     */
    public const NEW_PASSWORD = 'event.security.new_password';

    /**
     * @Event("Claroline\LogBundle\Event\Security\ForgotPasswordEvent")
     */
    public const FORGOT_PASSWORD = 'event.security.forgot_password';

    /**
     * @Event("Claroline\LogBundle\Event\Security\AddRoleEvent")
     */
    public const ADD_ROLE = 'event.security.add_role';

    /**
     * @Event("Claroline\LogBundle\Event\Security\RemoveRoleEvent")
     */
    public const REMOVE_ROLE = 'event.security.remove_role';

    /**
     * @Event("Claroline\LogBundle\Event\Security\ViewAsEvent")
     */
    public const VIEW_AS = 'event.security.view_as';

    /**
     * @Event("Claroline\LogBundle\Event\Security\ValidateEmailEvent")
     */
    public const VALIDATE_EMAIL = 'event.security.validate_email';

    /**
     * @Event("Claroline\LogBundle\Event\Security\AuthenticationFailureEvent")
     */
    public const AUTHENTICATION_FAILURE = 'event.security.authentication_failure';

    /**
     * @Event("Symfony\Component\Security\Http\Event\SwitchUserEvent")
     */
    public const SWITCH_USER = 'security.switch_user';
}
