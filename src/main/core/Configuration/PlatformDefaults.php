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

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;
use Claroline\CoreBundle\Security\PlatformRoles;

class PlatformDefaults implements ParameterProviderInterface
{
    public const REGISTRATION_MAIL_VALIDATION_NONE = 0;
    public const REGISTRATION_MAIL_VALIDATION_PARTIAL = 1;
    public const REGISTRATION_MAIL_VALIDATION_FULL = 2;

    public function getDefaultParameters()
    {
        return [
            'meta' => [],
            'home' => [
                'show_sub_menu' => false,
                'type' => 'none',
                'data' => null,
                'menu' => null,
            ],
            'profile' => [
                'roles_confidential' => [],
                'roles_locked' => [],
                'roles_edition' => [],
                'show_email' => [PlatformRoles::USER],
            ],
            'country' => '-',
            'logs' => [
                'enabled' => true,
            ],
            'community' => [ // to move in community parameters
                'username' => true,
            ],
            // database_restoration section is not configurable nor documented
            'database_restoration' => [
                'auto_validate_email' => false,
                'auto_enable_email_redirect' => false,
            ],
            'help' => [
                'url' => 'http://doc.claroline.com',
                'support_email' => null,
                'show' => true,
            ],
            'internet' => [
                'domain_name' => null,
                'platform_url' => null,
            ],
            'registration' => [ // to move in community parameters
                'self' => false,
                'default_role' => PlatformRoles::USER,
                'validation' => self::REGISTRATION_MAIL_VALIDATION_PARTIAL,
                'allow_workspace' => false,
                'organization_selection' => 'none',
                'username_regex' => "/^[a-zA-Z0-9@\-_\.]*$/",
            ],
            'security' => [ // to move in community parameters
                'account_duration' => null,
            ],
            'session' => [
                'storage_type' => 'file',
                'redis_host' => 'localhost',
                'redis_port' => '6379',
                'redis_password' => '',
            ],
            'display' => [
                'logo' => 'logo-sm.svg',
                'theme' => 'claroline',
                'resource_icon_set' => 'claroline',
                'name' => 'Claroline Connect',
                'secondary_name' => 'Easy & flexible learning',
                'name_active' => true,
                'breadcrumb' => true,
            ],
            'footer' => [
                'show' => true,
                'content' => null,
                'show_locale' => false,
                'show_help' => false,
                'show_terms_of_service' => false,
            ],
            'mailer' => [
                'enabled' => true,
                'transport' => 'sendmail',
                'host' => null,
                'port' => null,
                'encryption' => null,
                'username' => null,
                'password' => null,
                'api_key' => null,
                'tag' => null,
                'from' => 'noreply@claroline.com',
            ],
            'ssl' => [ // to remove
                'enabled' => false,
                'version' => 3,
            ],
            'locales' => [ // to move in `intl` block
                'default' => 'fr',
                'available' => ['fr', 'en'],
            ],
            'intl' => [
                'timezone' => null, // default to UTC
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
            ],
            'maintenance' => [
                'enable' => false,
                'message' => null,
            ],
            'header' => [
                'search' => [
                    'order' => 1,
                ],
                'history' => [
                    'order' => 2,
                ],
                'favourites' => [
                    'order' => 3,
                ],
                'notifications' => [
                    'order' => 4,
                ],
            ],
            'search' => [
                'limit' => 5,
                'items' => [
                    'user' => true,
                    'workspace' => true,
                    'resource' => true,
                ],
            ],
            'admin' => [ // to remove
                'default_tool' => 'home',
                'menu' => null,
            ],
            'desktop' => [ // to remove
                'default_tool' => 'home',
                'show_progression' => false,
                'menu' => null,
            ],
            'show_about_button' => true,
            'notifications_refresh_delay' => 0, // in ms
            'javascripts' => [],
            'restrictions' => [
                'users' => null,
                'storage' => null,
                'used_storage' => null,
            ],
            'pricing' => [
                'enabled' => false,
                'currency' => 'euro',
            ],
            'geoip' => [
                'maxmind_license_key' => null,
            ],
            'job_queue' => [
                'enabled' => false,
            ],
            'changelogMessage' => [
                // display a connection message when a new minor version is installed
                'enabled' => true,
                // how many times the changelog is displayed
                'duration' => 'P7D',
                // which roles see the changelog message
                'roles' => ['ROLE_ADMIN'],
            ],
            // a list of file mime types disallowed on the whole platform
            'file_blacklist' => [],
            // allow embedded javascript in TinyMCE contents
            'rich_text_script' => true,
        ];
    }
}
