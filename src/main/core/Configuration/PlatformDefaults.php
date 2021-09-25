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

class PlatformDefaults implements ParameterProviderInterface
{
    const REGISTRATION_MAIL_VALIDATION_NONE = 0;
    const REGISTRATION_MAIL_VALIDATION_PARTIAL = 1;
    const REGISTRATION_MAIL_VALIDATION_FULL = 2;

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
                'show_email' => ['ROLE_USER'],
            ],
            'country' => '-',
            'text' => [
                'enable_opengraph' => true,
            ],
            'logs' => [
                'enabled' => true,
            ],
            //database_restoration section is not configurable nor documented
            'database_restoration' => [
                'auto_validate_email' => false,
                'auto_enable_email_redirect' => false,
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
                'default_tag' => null,
            ],
            'registration' => [
                'self' => false,
                'default_role' => 'ROLE_USER',
                'validation' => self::REGISTRATION_MAIL_VALIDATION_PARTIAL,
                'auto_logging' => false,
                'allow_workspace' => false,
                'organization_selection' => 'none',
                'username_regex' => "/^[a-zA-Z0-9@\-_\.]*$/",
            ],
            'security' => [
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
            'admin' => [
                'default_tool' => 'home',
                'menu' => null,
            ],
            'desktop' => [
                'default_tool' => 'home',
                'show_progression' => false,
                'menu' => null,
            ],
            'show_about_button' => true,
            'notifications_refresh_delay' => 0, // in ms
            'is_cron_configured' => false,
            'javascripts' => [],
            'restrictions' => [
                'users' => false,
                'storage' => false,
                'max_users' => null,
                'max_storage_size' => null,
                'max_storage_reached' => false,
                'used_storage' => null,
            ],
            'privacy' => [
                'countryStorage' => null,
                'dpo' => [
                    'name' => null,
                    'email' => null,
                    'address' => [
                        'street1' => null,
                        'street2' => null,
                        'postalCode' => null,
                        'city' => null,
                        'state' => null,
                        'country' => null,
                    ],
                ],
            ],
            'pricing' => [
                'enabled' => false,
                'currency' => 'euro',
            ],
            'geoip' => [
                'maxmind_license_key' => null,
            ],
            'job_queue' => [
                'enabled' => true,
            ],
        ];
    }
}
