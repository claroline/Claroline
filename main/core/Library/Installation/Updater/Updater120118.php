<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class Updater120118 extends Updater
{
    protected $logger;

    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateListConfig();
        $this->saveConfigAsJson();
    }

    private function updateListConfig()
    {
        $this->log('Remove sorting of filters from list config (format has changed).');

        $this->conn
            ->prepare('UPDATE claro_directory SET sortBy = null, availableSort = "[]", filters = "[]", availableFilters = "[]"')
            ->execute();

        $this->conn
            ->prepare('UPDATE claro_widget_list SET sortBy = null, availableSort = "[]", filters = "[]", availableFilters = "[]"')
            ->execute();
    }

    public function saveConfigAsJson()
    {
        $data = $this->serialize();
        $data = json_encode($data, JSON_PRETTY_PRINT);
        $path = $this->container->getParameter('claroline.param.platform_options');
        $this->log('Saving config as json file');
        file_put_contents($path, $data);
    }

    public function serialize(array $options = [])
    {
        $parameters = Yaml::parse(file_get_contents($this->container->getParameter('claroline.param.platform_options_file'))) ?: [];

        $platformInitDate = new \DateTime();
        $platformInitDate->setTimeStamp($parameters['platform_init_date']);

        $platformLimitDate = new \DateTime();
        $platformLimitDate->setTimeStamp($parameters['platform_limit_date']);

        $serialized = [
                'display' => [
                    'footer' => $parameters['footer'],
                    'logo' => $parameters['logo'],
                    'theme' => $parameters['theme'],
                    'home_menu' => $parameters['home_menu'],
                    'footer_login' => $parameters['footer_login'],
                    'footer_workspaces' => $parameters['footer_workspaces'],
                    'header_locale' => $parameters['header_locale'],
                    'resource_icon_set' => $parameters['resource_icon_set'],
                    'logo_redirect_home' => $parameters['logo_redirect_home'],
                    'name' => $parameters['name'],
                    'secondary_name' => $parameters['secondary_name'],
                    'name_active' => $parameters['name_active'],
                ],
                'mailer' => [
                    'transport' => $parameters['mailer_transport'],
                    'host' => $parameters['mailer_host'],
                    'port' => $parameters['mailer_port'],
                    'encryption' => $parameters['mailer_encryption'],
                    'username' => $parameters['mailer_username'],
                    'password' => $parameters['mailer_password'],
                    'auth_mode' => $parameters['mailer_auth_mode'],
                    'api_key' => $parameters['mailer_api_key'],
                    'tag' => $parameters['mailer_tag'],
                    'from' => $parameters['mailer_from'],
                ],
                'ssl' => [
                    'enabled' => $parameters['ssl_enabled'],
                    'version' => $parameters['ssl_version_value'],
                ],
                'server' => [
                    'tmp_dir' => $parameters['tmp_dir'],
                ],
                'session' => [
                    'storage_type' => $parameters['session_storage_type'],
                    'db_table' => $parameters['session_db_table'],
                    'db_id_col' => $parameters['session_db_id_col'],
                    'db_data_col' => $parameters['session_db_data_col'],
                    'db_time_col' => $parameters['session_db_time_col'],
                    'db_dsn' => $parameters['session_db_dsn'],
                    'db_user' => $parameters['session_db_user'],
                    'db_password' => $parameters['session_db_password'],
                ],
                'auto_enable_notifications' => $parameters['auto_enable_notifications'],
                'locales' => [
                    'available' => $parameters['locales'],
                    'default' => $parameters['locale_language'],
                ],
                'security' => [
                    'form_captcha' => $parameters['form_captcha'],
                    'form_honeypot' => $parameters['form_honeypot'],
                    'platform_limit_date' => DateNormalizer::normalize($platformLimitDate),
                    'platform_init_date' => DateNormalizer::normalize($platformInitDate),
                    'cookie_lifetime' => $parameters['cookie_lifetime'],
                    'account_duration' => $parameters['account_duration'],
                    'default_root_anon_id' => $parameters['default_root_anon_id'],
                    'anonymous_public_profile' => $parameters['anonymous_public_profile'],
                    'disabled_admin_tools' => ['technical_settings'],
                ],
                'tos' => [
                    'enabled' => $parameters['terms_of_service'],
                ],
                'registration' => [
                    'username_regex' => $parameters['username_regex'],
                    'self' => $parameters['allow_self_registration'],
                    'default_role' => $parameters['default_role'],
                    'register_button_at_login' => $parameters['register_button_at_login'],
                    'auto_logging' => $parameters['auto_logging_after_registration'],
                    'validation' => $parameters['registration_mail_validation'],
                    'force_organization_creation' => $parameters['force_organization_creation'],
                    'allow_workspace' => $parameters['allow_workspace_at_registration'],
                ],
                'authentication' => [
                    'redirect_after_login_option' => $parameters['redirect_after_login_option'],
                    'redirect_after_login_url' => $parameters['redirect_after_login_url'],
                    'login_target_route' => $parameters['login_target_route'],
                    //used by cas
                    'direct_third_party' => $parameters['direct_third_party_authentication'],
                ],
                'workspace' => [
                    'max_storage_size' => $parameters['max_storage_size'],
                    'max_upload_resources' => $parameters['max_upload_resources'],
                    'max_workspace_users' => $parameters['max_workspace_users'],
                    'default_tag' => $parameters['default_workspace_tag'],
                    'users_csv_by_full_name' => $parameters['workspace_users_csv_import_by_full_name'],
                    'send_mail_at_registration' => $parameters['send_mail_at_workspace_registration'],
                    'enable_rich_text_file_import' => $parameters['enable_rich_text_file_import'],
                    'list' => [
                      'default_mode' => $parameters['workspace_list_default_mode'],
                      'default_properties' => $parameters['workspace_list_default_properties'],
                    ],
                ],
                'internet' => [
                    'domain_name' => $parameters['domain_name'],
                    'platform_url' => $parameters['platform_url'],
                    'google_meta_tag' => $parameters['google_meta_tag'],
                ],
                'help' => [
                    'url' => $parameters['help_url'],
                    'show' => $parameters['show_help_button'],
                    'support_email' => $parameters['support_email'],
                ],
                'geolocation' => [
                    'google' => [
                        'geocoding_client_id' => $parameters['google_geocoding_client_id'],
                        'geocoding_signature' => $parameters['google_geocoding_signature'],
                        'geocoding_key' => $parameters['google_geocoding_key'],
                    ],
                ],
                'pdf' => [
                    'active' => $parameters['is_pdf_export_active'],
                ],
                'statistics' => [
                    'url' => $parameters['datas_sending_url'],
                    'confirmed' => $parameters['confirm_send_datas'],
                    'token' => $parameters['token'],
                ],
                //database_restoration section is not configurable nor documented
                'database_restoration' => [
                    'auto_validate_email' => $parameters['auto_validate_email'],
                    'auto_enable_email_redirect' => $parameters['auto_enable_email_redirect'],
                ],
                'logs' => [
                    'enabled' => $parameters['platform_log_enabled'],
                ],
                'text' => [
                    'enable_opengraph' => $parameters['enable_opengraph'],
                ],
                'portfolio' => [
                    'url' => $parameters['portfolio_url'],
                ],
                'resource' => [
                    'soft_delete' => $parameters['resource_soft_delete'],
                ],
                'portal' => [
                    'enabled_resources' => $parameters['portal_enabled_resources'],
                ],
                'country' => $parameters['country'],
                'profile' => [
                    'roles_confidential' => $parameters['profile_roles_confidential'],
                    'roles_locked' => $parameters['profile_roles_locked'],
                    'roles_edition' => $parameters['profile_roles_edition'],
                ],
                'maintenance' => [
                    'enable' => false,
                    'message' => 'change me',
                ],
            ];

        return $serialized;
    }
}
