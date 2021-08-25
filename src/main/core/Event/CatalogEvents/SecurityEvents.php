<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class SecurityEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Security\UserLoginEvent")
     */
    public const USER_LOGIN = 'security.user_login';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\UserLogoutEvent")
     */
    public const USER_LOGOUT = 'security.user_logout';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\UserEnableEvent")
     */
    public const USER_ENABLE = 'security.user_enable';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\UserDisableEvent")
     */
    public const USER_DISABLE = 'security.user_disable';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\NewPasswordEvent")
     */
    public const NEW_PASSWORD = 'security.new_password';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\ForgotPasswordEvent")
     */
    public const FORGOT_PASSWORD = 'security.forgot_password';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\AddRoleEvent")
     */
    public const ADD_ROLE = 'security.add_role';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\RemoveRoleEvent")
     */
    public const REMOVE_ROLE = 'security.remove_role';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\ViewAsEvent")
     */
    public const VIEW_AS = 'security.view_as';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\ValidateEmailEvent")
     */
    public const VALIDATE_EMAIL = 'security.validate_email';

    /**
     * @Event("Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent")
     */
    public const AUTHENTICATION_FAILURE = 'security.authentication_failure';

    /**
     * @Event("Symfony\Component\Security\Http\Event\SwitchUserEvent")
     */
    public const SWITCH_USER = 'security.switch_user';
}
