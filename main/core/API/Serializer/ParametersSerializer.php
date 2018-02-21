<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Library\Configuration\PlatformConfiguration;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Serializes platform parameters.
 *
 * @DI\Service("claroline.serializer.parameters")
 */
class ParametersSerializer
{
    const ALL = 'all';

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var SerializerProvider */
    private $serializer;

    /** @var FinderProvider */
    private $finder;

    /**
     * ParametersSerializer constructor.
     *
     * @DI\InjectParams({
     *     "config"     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "finder"     = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param PlatformConfigurationHandler $config
     * @param SerializerProvider           $serializer
     * @param FinderProvider               $finder
     */
    public function __construct(
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        FinderProvider $finder
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->finder = $finder;
    }

    /**
     * Serializes the parameters list.
     *
     * NOT EVERY PARAMETERS SERIALIZED YET, THESE ONLY COME FROM COREBUNDLE
     * SEARCH "ParameterProviderInterface" FOR THE OTHERS
     *
     * @param array $options - the theme to serialize
     *
     * @return array - the serialized representation of the parameters
     *
     * @throws \Exception
     */
    public function serialize(array $options = [])
    {
        $parameters = $this->config->getParameters();

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
              'name' => $parameters['name'],
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
              'platform_limit_date' => $parameters['platform_limit_date'],
              'platform_init_date' => $parameters['platform_init_date'],
              'cookie_lifetime' => $parameters['cookie_lifetime'],
              'account_duration' => $parameters['account_duration'],
              'default_root_anon_id' => $parameters['default_root_anon_id'],
              'anonymous_public_profile' => $parameters['anonymous_public_profile'],
          ],
          'tos' => [
              'enabled' => $parameters['terms_of_service'],
              'text' => $this->serializeTos(),
          ],
          'registration' => [
              'username_regex' => $parameters['username_regex'],
              'self' => $parameters['allow_self_registration'],
              'default_role' => $parameters['default_role'],
              'register_button_at_login' => $parameters['register_button_at_login'],
              'auto_logging' => $parameters['auto_logging_after_registration'],
              'validation' => $parameters['registration_mail_validation'],
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
        ];

        if (in_array(self::ALL, $options)) {
            throw new \Exception('not implemented yet');
        }

        return $serialized;
    }

    public function serializeTos()
    {
        $result = $this->finder->search(
            'Claroline\CoreBundle\Entity\Content',
            ['filters' => ['type' => 'termsOfService']],
            ['property' => 'content']
        )['data'];

        if (count($result) > 0) {
            return $result[0];
        } else {
            $content = new Content();
            $content->setType('termsOfService');

            return $this->serializer->serialize($content);
        }
    }

    public function deserializeUser(array $data)
    {
        $parameters = [];

        $this->deserializeLocale($parameters, $data);
        $this->deserializeTos($parameters, $data);
        $this->deserializeSecurity($parameters, $data);
        $this->deserializeRegistration($parameters, $data);
        $this->deserializeAuthentication($parameters, $data);

        return $parameters;
    }

    /**
     * Deserializes the parameters list.
     *
     * @param array $data - the data to deserialize
     *
     * @return PlatformConfiguration
     */
    public function deserialize(array $data)
    {
        $parameters = [];

        if (isset($data['display'])) {
            $this->deserializeDisplay($parameters, $data);
        }

        if (isset($data['mailer'])) {
            $this->deserializeMailer($parameters, $data);
        }

        if (isset($data['ssl'])) {
            $this->deserializeSsl($parameters, $data);
        }

        if (isset($data['server'])) {
            $this->deserializeServer($parameters, $data);
        }

        if (isset($data['session'])) {
            $this->deserializeSession($parameters, $data);
        }

        if (isset($data['auto_enable_notifications'])) {
            $parameters['auto_enable_notifications'] = $data['auto_enable_notifications'];
        }

        $this->deserializeLocale($parameters, $data);
        $this->deserializeTos($parameters, $data);
        $this->deserializeSecurity($parameters, $data);
        $this->deserializeRegistration($parameters, $data);
        $this->deserializeAuthentication($parameters, $data);

        if (isset($data['workspace'])) {
            $this->deserializeWorkspace($parameters, $data);
        }

        if (isset($data['internet'])) {
            $this->deserializeInternet($parameters, $data);
        }

        if (isset($data['help'])) {
            $this->deserializeHelp($parameters, $data);
        }

        if (isset($data['geolocation'])) {
            $this->deserializeGeolocation($parameters, $data);
        }

        if (isset($data['pdf'])) {
            $this->buildParameter('pdf.active', 'is_pdf_export_active', $parameters, $data);
        }

        if (isset($data['database_restoration'])) {
            $this->deserializeDatabaseRestoration($parameters, $data);
        }

        if (isset($data['logs'])) {
            $this->buildParameter('logs.enabled', 'platform_log_enabled', $parameters, $data);
        }

        if (isset($data['text'])) {
            $this->buildParameter('text.enable_opengraph', 'enable_opengraph', $parameters, $data);
        }

        if (isset($data['portfolio'])) {
            $this->buildParameter('portfolio.url', 'portfolio_url', $parameters, $data);
        }

        if (isset($data['resource'])) {
            $this->buildParameter('resource.soft_delete', 'resource_soft_delete', $parameters, $data);
        }

        if (isset($data->portal)) {
            $this->buildParameter('portal.enabled_resources', 'portal_enabled_resources', $parameters, $data);
        }

        if (isset($data->country)) {
            $parameters['country'] = $data->country;
        }

        return new PlatformConfiguration($parameters);
    }

    public function deserializeDisplay(array &$parameters, array $data)
    {
        $this->buildParameter('display.footer', 'footer', $parameters, $data);
        $this->buildParameter('display.logo', 'logo', $parameters, $data);
        $this->buildParameter('display.theme', 'theme', $parameters, $data);
        $this->buildParameter('display.home_menu', 'home_menu', $parameters, $data);
        $this->buildParameter('display.footer_login', 'footer_login', $parameters, $data);
        $this->buildParameter('display.footer_workspaces', 'footer_workspaces', $parameters, $data);
        $this->buildParameter('display.header_locale', 'header_locale', $parameters, $data);
        $this->buildParameter('display.resource_icon_set', 'resource_icon_set', $parameters, $data);
        $this->buildParameter('display.name', 'name', $parameters, $data);
        $this->buildParameter('display.name_active', 'name_active', $parameters, $data);
    }

    public function deserializeMailer(array &$parameters, array $data)
    {
        $this->buildParameter('mailer.transport', 'mailer_transport', $parameters, $data);
        $this->buildParameter('mailer.host', 'mailer_host', $parameters, $data);
        $this->buildParameter('mailer.port', 'mailer_port', $parameters, $data);
        $this->buildParameter('mailer.encryption', 'mailer_encryption', $parameters, $data);
        $this->buildParameter('mailer.username', 'mailer_username', $parameters, $data);
        $this->buildParameter('mailer.password', 'mailer_password', $parameters, $data);
        $this->buildParameter('mailer.auth_mode', 'mailer_auth_mode', $parameters, $data);
        $this->buildParameter('mailer.api_key', 'mailer_api_key', $parameters, $data);
        $this->buildParameter('mailer.tag', 'mailer_tag', $parameters, $data);
        $this->buildParameter('mailer.from', 'mailer_from', $parameters, $data);
    }

    public function deserializeSsl(array &$parameters, array $data)
    {
        $this->buildParameter('ssl.enabled', 'ssl_enabled', $parameters, $data);
        $this->buildParameter('ssl.version', 'ssl_version_value', $parameters, $data);
    }

    public function deserializeLocale(array &$parameters, array $data)
    {
        if (isset($data->locales)) {
            $this->buildParameter('locales.available', 'locales', $parameters, $data);
            $this->buildParameter('locales.default', 'locale_language', $parameters, $data);
        }
    }

    public function deserializeSession(array &$parameters, array $data)
    {
        $this->buildParameter('session.storage_type', 'session_storage_type', $parameters, $data);
        $this->buildParameter('session.db_table', 'session_db_table', $parameters, $data);
        $this->buildParameter('session.db_id_col', 'session_db_id_col', $parameters, $data);
        $this->buildParameter('session.db_data_col', 'session_db_data_col', $parameters, $data);
        $this->buildParameter('session.db_time_col', 'session_db_time_col', $parameters, $data);
        $this->buildParameter('session.db_dsn', 'session_db_dsn', $parameters, $data);
        $this->buildParameter('session.db_user', 'session_db_dsn', $parameters, $data);
        $this->buildParameter('session.db_password', 'session_db_password', $parameters, $data);
    }

    public function deserializeSecurity(array &$parameters, array $data)
    {
        if (isset($data['security'])) {
            $this->buildParameter('security.form_captcha', 'form_captcha', $parameters, $data);
            $this->buildParameter('security.form_honeypot', 'form_honeypot', $parameters, $data);
            $this->buildParameter('security.platform_limit_date', 'platform_limit_date', $parameters, $data);
            $this->buildParameter('security.platform_init_date', 'platform_init_date', $parameters, $data);
            $this->buildParameter('security.cookie_lifetime', 'cookie_lifetime', $parameters, $data);
            $this->buildParameter('security.account_duration', 'account_duration', $parameters, $data);
            $this->buildParameter('security.default_root_anon_id', 'default_root_anon_id', $parameters, $data);
            $this->buildParameter('security.anonymous_public_profile', 'anonymous_public_profile', $parameters, $data);
        }
    }

    public function deserializeServer(array &$parameters, array $data)
    {
        $this->buildParameter('server.tmp_dir', 'tmp_dir', $parameters, $data);
    }

    public function deserializeRegistration(array &$parameters, array $data)
    {
        if (isset($data['registration'])) {
            $this->buildParameter('registration.username_regex', 'username_regex', $parameters, $data);
            $this->buildParameter('registration.self', 'allow_self_registration', $parameters, $data);
            $this->buildParameter('registration.default_role', 'default_role', $parameters, $data);
            $this->buildParameter('registration.register_button_at_login', 'register_button_at_login', $parameters, $data);
            $this->buildParameter('registration.auto_logging', 'auto_logging_after_registration', $parameters, $data);
            $this->buildParameter('registration.validation', 'registration_mail_validation', $parameters, $data);
        }
    }

    public function deserializeAuthentication(array &$parameters, array $data)
    {
        if (isset($data['authentication'])) {
            $this->buildParameter('authentication.redirect_after_login_option', 'redirect_after_login_option', $parameters, $data);
            $this->buildParameter('authentication.redirect_after_login_url', 'redirect_after_login_url', $parameters, $data);
            $this->buildParameter('authentication.login_target_route', 'login_target_route', $parameters, $data);
            $this->buildParameter('authentication.direct_third_party', 'direct_third_party_authentication', $parameters, $data);
            $this->buildParameter('authentication.force_organization_creation', 'force_organization_creation', $parameters, $data);
        }
    }

    public function deserializeWorkspace(array &$parameters, array $data)
    {
        $this->buildParameter('workspace.max_storage_size', 'max_storage_size', $parameters, $data);
        $this->buildParameter('workspace.max_upload_resources', 'max_upload_resources', $parameters, $data);
        $this->buildParameter('workspace.max_workspace_users', 'max_workspace_users', $parameters, $data);
        $this->buildParameter('workspace.default_tag', 'default_workspace_tag', $parameters, $data);
        $this->buildParameter('workspace.users_csv_by_full_name', 'workspace_users_csv_import_by_full_name', $parameters, $data);
        $this->buildParameter('workspace.send_mail_at_registration', 'send_mail_at_workspace_registration', $parameters, $data);
        $this->buildParameter('workspace.enable_rich_text_file_import', 'enable_rich_text_file_import', $parameters, $data);
    }

    public function deserializeInternet(array &$parameters, array $data)
    {
        $this->buildParameter('internet.domain_name', 'domain_name', $parameters, $data);
        $this->buildParameter('internet.platform_url', 'platform_url', $parameters, $data);
        $this->buildParameter('internet.google_meta_tag', 'google_meta_tag', $parameters, $data);
    }

    public function deserializeHelp(array &$parameters, array $data)
    {
        $this->buildParameter('help.url', 'help_url', $parameters, $data);
        $this->buildParameter('help.show', 'show_help_button', $parameters, $data);
        $this->buildParameter('help.support_email', 'support_email', $parameters, $data);
    }

    public function deserializeGeolocation(array &$parameters, array $data)
    {
        if (isset($data['geolocation']['google'])) {
            $this->buildParameter('geolocation.google.geocoding_client_id', 'google_geocoding_client_id', $parameters, $data);
            $this->buildParameter('geolocation.google.geocoding_signature', 'google_geocoding_signature', $parameters, $data);
            $this->buildParameter('geolocation.google.geocoding_key', 'google_geocoding_key', $parameters, $data);
        }
    }

    public function serializeStatistics(array &$parameters, array $data)
    {
        $this->buildParameter('statistics.url', 'datas_sending_url', $parameters, $data);
        $this->buildParameter('statistics.confirmed', 'confirm_send_datas', $parameters, $data);
        $this->buildParameter('statistics.token', 'token', $parameters, $data);
    }

    public function deserializeDatabaseRestoration(array &$parameters, array $data)
    {
        $this->buildParameter('database_restoration.auto_validate_email', 'auto_validate_email', $parameters, $data);
        $this->buildParameter('database_restoration.auto_enable_email_redirect', 'auto_enable_email_redirect', $parameters, $data);
    }

    public function deserializeTos(array &$parameters, array $data)
    {
        if (isset($data['tos'])) {
            $this->buildParameter('tos.enabled', 'terms_of_service', $parameters, $data);
            $contentTos = $this->finder->fetch('Claroline\CoreBundle\Entity\Content', 0, 10, ['type' => 'termsOfService']);

            if (0 === count($contentTos)) {
                $contentTos = new Content();
                $contentTos->setType('termsOfService');
            } else {
                $contentTos = $contentTos[0];
            }

            $serializer = $this->serializer->get('Claroline\CoreBundle\Entity\Content');
            $serializer->deserialize($data['tos']['text'], $contentTos, ['property' => 'content']);
        }
    }

    private function buildParameter($serializedPath, $parametersPath, array &$parameters, array $data)
    {
        $value = $data;
        $keys = explode('.', $serializedPath);
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                //no key = keep old value and don't do anything
                return;
            }
        }

        $parameters[$parametersPath] = $value;
    }
}
