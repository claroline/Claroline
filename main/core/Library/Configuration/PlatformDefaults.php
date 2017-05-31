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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
            'name' => 'claroline',
            'name_active' => true,
            'support_email' => 'noreply@changeme.com',
            'footer' => null,
            'logo' => 'clarolineconnect.png',
            'allow_self_registration' => true,
            'locale_language' => 'fr',
            'theme' => 'claroline',
            'default_role' => 'ROLE_USER',
            'cookie_lifetime' => 3600,
            'mailer_transport' => 'sendmail',
            'mailer_host' => null,
            'mailer_port' => null,
            'mailer_encryption' => null,
            'mailer_username' => null,
            'mailer_password' => null,
            'mailer_auth_mode' => null,
            'terms_of_service' => true,
            'google_meta_tag' => null,
            'redirect_after_login_option' => self::DEFAULT_REDIRECT_OPTION,
            'redirect_after_login_url' => null,
            'session_storage_type' => 'native',
            'session_db_table' => null,
            'session_db_id_col' => null,
            'session_db_data_col' => null,
            'session_db_time_col' => null,
            'session_db_dsn' => null,
            'session_db_user' => null,
            'session_db_password' => null,
            'form_captcha' => true,
            'form_honeypot' => false,
            'platform_limit_date' => 1559350861, //1 june 2019
            'platform_init_date' => 1388534461, //1 june 2014
            'account_duration' => null,
            'username_regex' => '/^[a-zA-Z0-9@\-_\.]*$/',
            'anonymous_public_profile' => false,
            'home_menu' => null,
            'footer_login' => false,
            'footer_workspaces' => false,
            'header_locale' => false,
            'portfolio_url' => null,
            'max_storage_size' => Workspace::DEFAULT_MAX_STORAGE_SIZE,
            'max_upload_resources' => Workspace::DEFAULT_MAX_FILE_COUNT,
            'max_workspace_users' => Workspace::DEFAULT_MAX_USERS,
            'confirm_send_datas' => null,
            'token' => null,
            'country' => '-',
            'datas_sending_url' => 'http://stats.claroline.net/insert.php',
            'repository_api' => 'http://packages.claroline.net/api.php',
            'use_repository_test' => false,
            'auto_logging_after_registration' => false,
            'registration_mail_validation' => self::REGISTRATION_MAIL_VALIDATION_PARTIAL,
            'resource_soft_delete' => false,
            'show_help_button' => true,
            'help_url' => 'http://doc.claroline.com',
            'register_button_at_login' => false,
            'send_mail_at_workspace_registration' => true,
            'locales' => ['fr', 'en'],
            'domain_name' => null,
            'platform_url' => null,
            'mailer_from' => null,
            'default_workspace_tag' => null,
            'is_pdf_export_active' => false,
            'google_geocoding_client_id' => null,
            'google_geocoding_signature' => null,
            'google_geocoding_key' => null,
            'portal_enabled_resources' => null,
            'ssl_enabled' => false,
            'ssl_version_value' => 3,
            'enable_rich_text_file_import' => false,
            'login_target_route' => 'claro_security_login',
            'enable_opengraph' => true,
            'tmp_dir' => sys_get_temp_dir(),
            'resource_icon_set' => 'claroline',
            'direct_third_party_authentication' => false,
            'platform_log_enabled' => true,
            'workspace_users_csv_import_by_full_name' => false,
            'platform_log_enabled' => true,
        ];
    }
}
