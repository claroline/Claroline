<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\LegacyParametersMappingInterface;

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
            'account_duration' => 'security.account_duration',
            'username_regex' => 'registration.username_regex',
            'footer_login' => 'display.footer_login',
            'footer_workspaces' => 'display.footer_workspaces',
            'header_locale' => 'footer.show_locale',
            'token' => 'statistics.token',
            'country' => 'country',
            'auto_logging_after_registration' => 'registration.auto_logging',
            'registration_mail_validation' => 'registration.validation',
            'resource_soft_delete' => 'resource.soft_delete',
            'show_help_button' => 'help.show',
            'show_about_button' => 'show_about_button',
            'help_url' => 'help.url',
            'locales' => 'locale.available',
            'domain_name' => 'internet.domain_name',
            'platform_url' => 'internet.platform_url',
            'mailer_from' => 'mailer.from',
            'default_workspace_tag' => 'workspace.default_tag',
            'ssl_enabled' => 'ssl.enabled',
            'ssl_version_value' => 'ssl.version',
            'enable_opengraph' => 'text.enable_opengraph',
            'tmp_dir' => 'server.tmp_dir',
            'resource_icon_set' => 'display.resource_icon_set',
            'direct_third_party_authentication' => 'authentication.direct_third_party',
            'platform_log_enabled' => 'logs.enabled',
            'auto_validate_email' => 'database_restoration.auto_validate_email',
            'notifications_refresh_delay' => 'notifications_refresh_delay', // in ms
            'auto_enable_email_redirect' => 'database_restoration.auto_enable_email_redirect',
            'auto_enable_notifications' => 'auto_enable_notifications',
            'is_cron_configured' => 'is_cron_configured',
            'force_organization_creation' => 'registration.force_organization_creation',
            'allow_workspace_at_registration' => 'registration.allow_workspace',
            'profile_roles_confidential' => 'profile.roles_confidential',
            'profile_roles_locked' => 'profile.roles_locked',
            'profile_roles_edition' => 'profile.roles_edition',
            'profile_show_email' => 'profile.show_email',
            'home_redirection_type' => 'home.type',
            'home_redirection_url' => 'home.data',
        ];
    }
}
