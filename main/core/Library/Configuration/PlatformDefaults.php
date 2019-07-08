<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Configuration;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.configuration")
 */
class PlatformDefaults implements ParameterProviderInterface
{
    const REGISTRATION_MAIL_VALIDATION_NONE = 0;
    const REGISTRATION_MAIL_VALIDATION_PARTIAL = 1;
    const REGISTRATION_MAIL_VALIDATION_FULL = 2;
    const DEFAULT_REDIRECT_OPTION = 'DESKTOP';

    public static $REDIRECT_OPTIONS = [
        'DESKTOP' => 'DESKTOP',
        'LAST' => 'LAST',
        'URL' => 'URL',
        'WORKSPACE_TAG' => 'WORKSPACE_TAG',
    ];

    public function getDefaultParameters()
    {
        return [
            'home' => [
              'redirection_type' => null,
              'redirection_url' => null,
            ],
            'profile' => [
              'roles_confidential' => [],
              'roles_locked' => [],
              'roles_edition' => [],
              'show_email' => ['ROLE_USER'],
            ],
            'country' => '-',
            'portfolio' => [
                'url' => null,
            ],
            'text' => [
              'enable_opengraph' => true,
            ],
            'swagger' => [
              'base' => '/Claroline/Claroline/web/app_dev.php',
            ],
            'logs' => [
              'enabled' => true,
            ],
            //database_restoration section is not configurable nor documented
            'database_restoration' => [
                'auto_validate_email' => false,
                'auto_enable_email_redirect' => false,
            ],
            'statistics' => [
              'url' => 'http://stats.claroline.net/insert.php',
              'token' => null,
              'confirmed' => null,
            ],
            'pdf' => [
              'active' => false,
            ],
            'geolocation' => [
              'google' => [
                'geocoding_client_id' => null,
                'geocoding_signature' => null,
                'geocoding_key' => null,
              ],
            ],
            'help' => [
              'url' => 'http://doc.claroline.com',
              'support_email' => 'noreply@claroline.com',
              'show' => true,
            ],
            'tos' => [
              'enabled' => true,
            ],
            'internet' => [
              'domain_name' => null,
              'platform_url' => null,
              'google_meta_tag' => null,
            ],
            'workspace' => [
              'max_storage_size' => '1 TB',
              'max_upload_resources' => 10000,
              'max_workspace_users' => 10000,
              'enable_rich_text_file_import' => false,
              'send_mail_at_registration' => true,
              'users_csv_by_full_name' => false, //is it still used ?
              'default_tag' => null,
              'list' => [
                'default_mode' => 'tiles-sm',
                'default_properties' => [
                    'name',
                    'code',
                    'registration.selfRegistration',
                    'registration.waitingForRegistration',
                ],
              ],
            ],
            'authentication' => [
              'redirect_after_login_option' => self::DEFAULT_REDIRECT_OPTION,
              'redirect_after_login_url' => null,
              'direct_third_party' => false,
              'login_target_route' => 'claro_security_login',
            ],
            'registration' => [
              'self' => false,
              'default_role' => 'ROLE_USER',
              'validation' => self::REGISTRATION_MAIL_VALIDATION_PARTIAL,
              'auto_logging' => false,
              'register_button_at_login' => false,
              'allow_workspace' => false,
              'username_regex' => "/^[a-zA-Z0-9@\-_\.]*$/",
              'force_organization_creation' => false,
            ],
            'security' => [
              'form_captcha' => true,
              'form_honeypot' => false,
              'platform_limit_date' => null,
              'platform_init_date' => 1388534461, //1 june 2014
              'cookie_lifetime' => 3600,
              'account_duration' => null,
              'default_root_anon_id' => null,
              'anonymous_public_profile' => false,
              'disabled_admin_tools' => [
              ],
            ],
            'session' => [
              'storage_type' => 'native',
              'db_table' => null,
              'db_id_col' => null,
              'db_data_col' => null,
              'db_time_col' => null,
              'db_dsn' => null,
              'db_user' => null,
              'db_password' => null,
            ],
            'display' => [
              'footer' => null,
              'logo' => 'logo-sm.svg',
              'theme' => 'claroline',
              'home_menu' => null,
              'footer_login' => false,
              'footer_workspaces' => false,
              'header_locale' => true,
              'resource_icon_set' => 'claroline',
              'name' => 'Claroline Connect',
              'secondary_name' => 'Easy & flexible learning',
              'logo_redirect_home' => true,
              'name_active' => true,
            ],
            'mailer' => [
              'transport' => 'sendmail',
              'host' => null,
              'port' => null,
              'encryption' => null,
              'username' => null,
              'password' => null,
              'auth_mode' => null,
              'api_key' => null,
              'tag' => null,
              'from' => null,
            ],
            'ssl' => [
              'enabled' => false,
              'version' => 3,
            ],
            'server' => [
              'tmp_dir' => sys_get_temp_dir(),
            ],
            'auto_enable_notifications' => [
              'resource-create' => ['visible'],
              'resource-publish' => ['visible'],
              'role-change_right' => ['visible'],
              'role-subscribe' => ['visible'],
              'badge-award' => ['visible'],
              'resource-text' => ['visible'],
              'forum' => ['visible'],
              'portfolio' => ['visible'],
              'icap_blog' => ['visible'],
              'icap_dropzone' => ['visible'],
              'icap_socialmedia' => ['visible'],
              'icap_wiki' => ['visible'],
              'innova_path' => ['visible'],
              'icap_lesson' => ['visible'],
            ],
            'locales' => [
              'default' => 'fr',
              'available' => ['fr', 'en'],
            ],
            'maintenance' => [
              'enable' => false,
              'message' => null,
            ],

            'header_menu' => 'workspaces',
            'show_about_button' => true,
            //not documented, for the cli tool claroline:user:mailing
            'notifications_refresh_delay' => 12000, // in ms
            'is_cron_configured' => false,
            'javascripts' => [],
        ];
    }
}
