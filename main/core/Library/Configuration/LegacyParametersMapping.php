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

class LegacyParametersMapping implements LegacyParametersMappingInterface
{
    public function getMapping()
    {
        return [
            'name' => 'display.name',
            'secondary_name' => 'display.secondary_name',
            'name_active' => 'display.name_active',
            'support_email' => 'help.support_email',
            'footer' => 'footer.content',
            'logo' => 'display.logo',
            'allow_self_registration' => 'registration.self',
            'locale_language' => 'locales.default',
            'theme' => 'display.theme',
            'default_role' => 'registration.default_role',
            'cookie_lifetime' => 'security.cookie_lifetime',
            'mailer_transport' => 'mailer.transport',
            'mailer_host' => 'mailer.host',
            'mailer_port' => 'mailer.port',
            'mailer_encryption' => 'mailer.encryption',
            'mailer_username' => 'mailer.username',
            'mailer_password' => 'mailer.password',
            'mailer_auth_mode' => 'mailer.auth_mode',
            'mailer_api_key' => 'mailer.api_key',
            'mailer_tag' => 'mailer.tag',
            'terms_of_service' => 'tos.enabled',
            'google_meta_tag' => 'internet.google_meta_tag',
            'redirect_after_login_option' => 'authentication.redirect_after_login_option',
            'redirect_after_login_url' => 'authentication.redirect_after_login_url',
            'session_storage_type' => 'session.storage_type',
            'session_db_table' => 'session.db_table',
            'session_db_id_col' => 'session.db_id_col',
            'session_db_data_col' => 'session.db_data_col',
            'session_db_time_col' => 'session.db_time_col',
            'session_db_dsn' => 'session.db_dsn',
            'session_db_user' => 'session.db_user',
            'session_db_password' => 'session.db_password',
            'form_captcha' => 'security.form_captcha',
            'form_honeypot' => 'security.form_honeypot',
            'platform_limit_date' => 'security.platform_limit_date', //1 june 2019
            'platform_init_date' => 'security.platform_init_date', //1 june 2014
            'account_duration' => 'security.account_duration',
            'username_regex' => 'registration.username_regex',
            'anonymous_public_profile' => 'security.anonymous_public_profile',
            'home_menu' => 'display.home_menu',
            'footer_login' => 'display.footer_login',
            'footer_workspaces' => 'display.footer_workspaces',
            'header_locale' => 'footer.show_locale',
            'header_menu' => 'header_menu',
            'portfolio_url' => 'portfolio.url',
            'max_storage_size' => 'workspace.max_storage_size',
            'max_upload_resources' => 'workspace.max_upload_resources',
            'max_workspace_users' => 'workspace.max_workspace_users',
            'confirm_send_datas' => 'statistics.confirmed',
            'token' => 'statistics.token',
            'country' => 'country',
            'datas_sending_url' => 'statistics.url',
            'auto_logging_after_registration' => 'registration.auto_logging',
            'registration_mail_validation' => 'registration.validation',
            'resource_soft_delete' => 'resource.soft_delete',
            'show_help_button' => 'help.show',
            'show_about_button' => 'show_about_button',
            'help_url' => 'help.url',
            'register_button_at_login' => 'registration.register_button_at_login',
            'send_mail_at_workspace_registration' => 'workspace.send_mail_at_registration',
            'locales' => 'locale.available',
            'domain_name' => 'internet.domain_name',
            'platform_url' => 'internet.platform_url',
            'mailer_from' => 'mailer.from',
            'default_workspace_tag' => 'workspace.default_tag',
            'google_geocoding_client_id' => 'geolocation.google.geocoding_client_id',
            'google_geocoding_signature' => 'geolocation.google.geocoding_signature',
            'google_geocoding_key' => 'geolocation.google.geocoding_key',
            'ssl_enabled' => 'ssl.enabled',
            'ssl_version_value' => 'ssl.version',
            'enable_rich_text_file_import' => 'workspace.enable_rich_text_file_import',
            'enable_opengraph' => 'text.enable_opengraph',
            'tmp_dir' => 'server.tmp_dir',
            'resource_icon_set' => 'display.resource_icon_set',
            'direct_third_party_authentication' => 'authentication.direct_third_party',
            'workspace_users_csv_import_by_full_name' => 'workspace.users_csv_import_by_full_name',
            'platform_log_enabled' => 'logs.enabled',
            //not documented, for the cli tool claroline:user:mailing
            'auto_validate_email' => 'database_restoration.auto_validate_email',
            'notifications_refresh_delay' => 'notifications_refresh_delay', // in ms
            'auto_enable_email_redirect' => 'database_restoration.auto_enable_email_redirect',
            'auto_enable_notifications' => 'auto_enable_notifications',
            'default_root_anon_id' => 'security.default_root_anon_id',
            'is_cron_configured' => 'is_cron_configured',
            'force_organization_creation' => 'registration.force_organization_creation',
            'allow_workspace_at_registration' => 'registration.allow_workspace',
            'profile_roles_confidential' => 'profile.roles_confidential',
            'profile_roles_locked' => 'profile.roles_locked',
            'profile_roles_edition' => 'profile.roles_edition',
            'profile_show_email' => 'profile.show_email',
            'workspace_list_default_properties' => 'workspace.list.default_properties',
            'home_redirection_type' => 'home.type',
            'home_redirection_url' => 'home.data',
        ];
    }
}
