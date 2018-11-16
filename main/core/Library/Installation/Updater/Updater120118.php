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
                    'footer' => $this->setParameter($parameters, 'footer'),
                    'logo' => $this->setParameter($parameters, 'logo'),
                    'theme' => $this->setParameter($parameters, 'theme'),
                    'home_menu' => $this->setParameter($parameters, 'home_menu'),
                    'footer_login' => $this->setParameter($parameters, 'footer_login'),
                    'footer_workspaces' => $this->setParameter($parameters, 'footer_workspaces'),
                    'header_locale' => $this->setParameter($parameters, 'header_locale'),
                    'resource_icon_set' => $this->setParameter($parameters, 'resource_icon_set'),
                    'logo_redirect_home' => $this->setParameter($parameters, 'logo_redirect_home'),
                    'name' => $this->setParameter($parameters, 'name'),
                    'secondary_name' => $this->setParameter($parameters, 'secondary_name'),
                    'name_active' => $this->setParameter($parameters, 'name_active'),
                ],
                'mailer' => [
                    'transport' => $this->setParameter($parameters, 'mailer_transport'),
                    'host' => $this->setParameter($parameters, 'mailer_host'),
                    'port' => $this->setParameter($parameters, 'mailer_port'),
                    'encryption' => $this->setParameter($parameters, 'mailer_encryption'),
                    'username' => $this->setParameter($parameters, 'mailer_username'),
                    'password' => $this->setParameter($parameters, 'mailer_password'),
                    'auth_mode' => $this->setParameter($parameters, 'mailer_auth_mode'),
                    'api_key' => $this->setParameter($parameters, 'mailer_api_key'),
                    'tag' => $this->setParameter($parameters, 'mailer_tag'),
                    'from' => $this->setParameter($parameters, 'mailer_from'),
                ],
                'ssl' => [
                    'enabled' => $this->setParameter($parameters, 'ssl_enabled'),
                    'version' => $this->setParameter($parameters, 'ssl_version_value'),
                ],
                'server' => [
                    'tmp_dir' => $this->setParameter($parameters, 'tmp_dir'),
                ],
                'session' => [
                    'storage_type' => $this->setParameter($parameters, 'session_storage_type'),
                    'db_table' => $this->setParameter($parameters, 'session_db_table'),
                    'db_id_col' => $this->setParameter($parameters, 'session_db_id_col'),
                    'db_data_col' => $this->setParameter($parameters, 'session_db_data_col'),
                    'db_time_col' => $this->setParameter($parameters, 'session_db_time_col'),
                    'db_dsn' => $this->setParameter($parameters, 'session_db_dsn'),
                    'db_user' => $this->setParameter($parameters, 'session_db_user'),
                    'db_password' => $this->setParameter($parameters, 'session_db_password'),
                ],
                'auto_enable_notifications' => $this->setParameter($parameters, 'auto_enable_notifications'),
                'locales' => [
                    'available' => $this->setParameter($parameters, 'locales'),
                    'default' => $this->setParameter($parameters, 'locale_language'),
                ],
                'security' => [
                    'form_captcha' => $this->setParameter($parameters, 'form_captcha'),
                    'form_honeypot' => $this->setParameter($parameters, 'form_honeypot'),
                    'platform_limit_date' => DateNormalizer::normalize($platformLimitDate),
                    'platform_init_date' => DateNormalizer::normalize($platformInitDate),
                    'cookie_lifetime' => $this->setParameter($parameters, 'cookie_lifetime'),
                    'account_duration' => $this->setParameter($parameters, 'account_duration'),
                    'default_root_anon_id' => $this->setParameter($parameters, 'default_root_anon_id'),
                    'anonymous_public_profile' => $this->setParameter($parameters, 'anonymous_public_profile'),
                    'disabled_admin_tools' => ['technical_settings'],
                ],
                'tos' => [
                    'enabled' => $this->setParameter($parameters, 'terms_of_service'),
                ],
                'registration' => [
                    'username_regex' => $this->setParameter($parameters, 'username_regex'),
                    'self' => $this->setParameter($parameters, 'allow_self_registration'),
                    'default_role' => $this->setParameter($parameters, 'default_role'),
                    'register_button_at_login' => $this->setParameter($parameters, 'register_button_at_login'),
                    'auto_logging' => $this->setParameter($parameters, 'auto_logging_after_registration'),
                    'validation' => $this->setParameter($parameters, 'registration_mail_validation'),
                    'force_organization_creation' => $this->setParameter($parameters, 'force_organization_creation'),
                    'allow_workspace' => $this->setParameter($parameters, 'allow_workspace_at_registration'),
                ],
                'authentication' => [
                    'redirect_after_login_option' => $this->setParameter($parameters, 'redirect_after_login_option'),
                    'redirect_after_login_url' => $this->setParameter($parameters, 'redirect_after_login_url'),
                    'login_target_route' => $this->setParameter($parameters, 'login_target_route'),
                    //used by cas
                    'direct_third_party' => $this->setParameter($parameters, 'direct_third_party_authentication'),
                ],
                'workspace' => [
                    'max_storage_size' => $this->setParameter($parameters, 'max_storage_size'),
                    'max_upload_resources' => $this->setParameter($parameters, 'max_upload_resources'),
                    'max_workspace_users' => $this->setParameter($parameters, 'max_workspace_users'),
                    'default_tag' => $this->setParameter($parameters, 'default_workspace_tag'),
                    'users_csv_by_full_name' => $this->setParameter($parameters, 'workspace_users_csv_import_by_full_name'),
                    'send_mail_at_registration' => $this->setParameter($parameters, 'send_mail_at_workspace_registration'),
                    'enable_rich_text_file_import' => $this->setParameter($parameters, 'enable_rich_text_file_import'),
                    'list' => $this->setParameter($parameters, 'workspace.list'),
                ],
                'internet' => [
                    'domain_name' => $this->setParameter($parameters, 'domain_name'),
                    'platform_url' => $this->setParameter($parameters, 'platform_url'),
                    'google_meta_tag' => $this->setParameter($parameters, 'google_meta_tag'),
                ],
                'help' => [
                    'url' => $this->setParameter($parameters, 'help_url'),
                    'show' => $this->setParameter($parameters, 'show_help_button'),
                    'support_email' => $this->setParameter($parameters, 'support_email'),
                ],
                'geolocation' => [
                    'google' => [
                        'geocoding_client_id' => $this->setParameter($parameters, 'google_geocoding_client_id'),
                        'geocoding_signature' => $this->setParameter($parameters, 'google_geocoding_signature'),
                        'geocoding_key' => $this->setParameter($parameters, 'google_geocoding_key'),
                    ],
                ],
                'pdf' => [
                    'active' => $this->setParameter($parameters, 'is_pdf_export_active'),
                ],
                'statistics' => [
                    'url' => $this->setParameter($parameters, 'datas_sending_url'),
                    'confirmed' => $this->setParameter($parameters, 'confirm_send_datas'),
                    'token' => $this->setParameter($parameters, 'token'),
                ],
                //database_restoration section is not configurable nor documented
                'database_restoration' => [
                    'auto_validate_email' => $this->setParameter($parameters, 'auto_validate_email'),
                    'auto_enable_email_redirect' => $this->setParameter($parameters, 'auto_enable_email_redirect'),
                ],
                'logs' => [
                    'enabled' => $this->setParameter($parameters, 'platform_log_enabled'),
                ],
                'text' => [
                    'enable_opengraph' => $this->setParameter($parameters, 'enable_opengraph'),
                ],
                'portfolio' => [
                    'url' => $this->setParameter($parameters, 'portfolio_url'),
                ],
                'resource' => [
                    'soft_delete' => $this->setParameter($parameters, 'resource_soft_delete'),
                ],
                'portal' => [
                    'enabled_resources' => $this->setParameter($parameters, 'portal_enabled_resources'),
                ],
                'country' => $this->setParameter($parameters, 'country'),
                'profile' => [
                    'roles_confidential' => $this->setParameter($parameters, 'profile_roles_confidential'),
                    'roles_locked' => $this->setParameter($parameters, 'profile_roles_locked'),
                    'roles_edition' => $this->setParameter($parameters, 'profile_roles_edition'),
                ],
                'maintenance' => [
                    'enable' => false,
                    'message' => 'change me',
                ],
            ];

        return $serialized;
    }

    private function setParameter($parameters, $name)
    {
        if (isset($parameters[$name])) {
            return $parameters[$name];
        }

        return $this->container->get('claroline.config.platform_config_handler')->getParameter($name);
    }
}
