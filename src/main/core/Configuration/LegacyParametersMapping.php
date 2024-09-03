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
    public function getMapping(): array
    {
        return [
            'name' => 'display.name',
            'support_email' => 'help.support_email',
            'footer' => 'footer.content',
            'logo' => 'display.logo',
            'theme' => 'display.theme',
            'default_role' => 'registration.default_role',
            'account_duration' => 'security.account_duration',
            'username_regex' => 'registration.username_regex',
            'country' => 'country',
            'registration_mail_validation' => 'registration.validation',
            'show_help_button' => 'help.show',
            'help_url' => 'help.url',
            'locales' => 'locale.available',
            'domain_name' => 'internet.domain_name',
            'platform_url' => 'internet.platform_url',
            'resource_icon_set' => 'display.resource_icon_set',
            'auto_validate_email' => 'database_restoration.auto_validate_email',
            'auto_enable_email_redirect' => 'database_restoration.auto_enable_email_redirect',
            'force_organization_creation' => 'registration.force_organization_creation',
            'profile_roles_confidential' => 'profile.roles_confidential',
            'profile_roles_locked' => 'profile.roles_locked',
            'profile_roles_edition' => 'profile.roles_edition',
            'profile_show_email' => 'profile.show_email',
            'home_redirection_type' => 'home.type',
            'home_redirection_url' => 'home.data',
        ];
    }
}
