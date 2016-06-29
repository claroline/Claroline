<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebInstaller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Filesystem\Filesystem;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Claroline\CoreBundle\Library\Installation\Settings\DatabaseChecker;
use Claroline\CoreBundle\Library\Installation\Settings\MailingChecker;

class Controller
{
    private $container;
    private $request;
    private $parameters;
    private $configHandler;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request = $this->container->getRequest();
        $this->parameters = $this->container->getParameterBag();
        $ds = DIRECTORY_SEPARATOR;
        $configFile = $container->getAppDirectory().
            $ds.'config'.$ds.'platform_options.yml';
        $lockedFile = $container->getAppDirectory().
            $ds.'config'.$ds.'locked_platform_options.yml';
        $this->parametersDist = $container->getAppDirectory().
            $ds.'config'.$ds.'parameters.yml.dist';
        $this->configHandler = new PlatformConfigurationHandler($configFile, $lockedFile);
    }

    public function languageStep()
    {
        return $this->renderStep(
            'language.html.php',
            'welcome',
            array(
                'install_language' => $this->parameters->getInstallationLanguage(),
                'country' => $this->parameters->getCountry(),
            )
        );
    }

    public function languageStepSubmit()
    {
        $language = $this->request->request->get('install_language');
        $this->parameters->setInstallationLanguage($language);
        $this->parameters->setCountry($this->request->request->get('country'));
        $this->container->getTranslator()->setLanguage($language);

        return $this->redirect('/');
    }

    public function requirementStep()
    {
        $settingChecker = new SettingChecker();
        $ds = DIRECTORY_SEPARATOR;

        $fs = new Filesystem();
        $fs->copy($this->parametersDist, $this->container->getAppDirectory().
            $ds.'config'.$ds.'parameters.yml');

        return $this->renderStep(
            'requirements.html.php',
            'requirements_check',
            array(
                'setting_categories' => $settingChecker->getSettingCategories(),
                'has_failed_recommendation' => $settingChecker->hasFailedRecommendation(),
                'has_failed_requirement' => $settingChecker->hasFailedRequirement(),
            )
        );
    }

    public function databaseStep()
    {
        return $this->renderStep(
            'database.html.php',
            'database_parameters',
            array(
                'settings' => $this->parameters->getDatabaseSettings(),
                'global_error' => $this->parameters->getDatabaseGlobalError(),
                'validation_errors' => $this->parameters->getDatabaseValidationErrors(),
            )
        );
    }

    public function databaseStepSubmit()
    {
        $postSettings = $this->request->request->all();
        $databaseSettings = $this->parameters->getDatabaseSettings();
        $databaseSettings->bindData($postSettings);
        $errors = $databaseSettings->validate();
        $this->parameters->setDatabaseValidationErrors($errors);

        if (count($errors) > 0) {
            return $this->redirect('/database');
        }

        $checker = new DatabaseChecker($databaseSettings);
        $status = $checker->connectToDatabase();

        if (true !== $status && !(isset($postSettings['force_install']) && $status === 'not_empty_database')) {
            $this->parameters->setDatabaseGlobalError($status);

            return $this->redirect('/database');
        }

        $this->parameters->setDatabaseGlobalError(null);

        return $this->redirect('/platform');
    }

    public function platformStep()
    {
        $platformSettings = $this->parameters->getPlatformSettings();

        if (!$platformSettings->getLanguage()) {
            $platformSettings->setLanguage($this->parameters->getInstallationLanguage());
        }

        return $this->renderStep(
            'platform.html.php',
            'platform_parameters',
            array(
                'platform_settings' => $platformSettings,
                'errors' => $this->parameters->getPlatformValidationErrors(),
            )
        );
    }

    public function platformSubmitStep()
    {
        $postSettings = $this->request->request->all();
        $platformSettings = $this->parameters->getPlatformSettings();
        $platformSettings->bindData($postSettings);
        $errors = $platformSettings->validate();
        $this->parameters->setPlatformValidationErrors($errors);

        if (count($errors) > 0) {
            return $this->redirect('/platform');
        }

        return $this->redirect('/admin');
    }

    public function adminUserStep()
    {
        return $this->renderStep(
            'admin.html.php',
            'admin_user',
            array(
                'first_admin_settings' => $this->parameters->getFirstAdminSettings(),
                'errors' => $this->parameters->getFirstAdminValidationErrors(),
            )
        );
    }

    public function adminUserStepSubmit()
    {
        $postSettings = $this->request->request->all();
        $adminSettings = $this->parameters->getFirstAdminSettings();
        $adminSettings->bindData($postSettings);
        $errors = $adminSettings->validate();
        $this->parameters->setFirstAdminValidationErrors($errors);

        if (count($errors) > 0) {
            return $this->redirect('/admin');
        }

        return $this->redirect('/mailing');
    }

    public function mailingStep()
    {
        return $this->renderStep(
            'mailing.html.php',
            'mail_server',
            array(
                'mailing_settings' => $this->parameters->getMailingSettings(),
                'global_error' => $this->parameters->getMailingGlobalError(),
                'validation_errors' => $this->parameters->getMailingValidationErrors(),
            )
        );
    }

    public function mailingStepSubmit()
    {
        $postSettings = $this->request->request->all();
        $mailingSettings = $this->parameters->getMailingSettings();
        $transportId = $this->getTransportId($postSettings['transport']);

        if ($transportId !== $mailingSettings->getTransport()) {
            $mailingSettings->setTransport($transportId);
            $mailingSettings->setTransportOptions(array());
            $this->parameters->setMailingGlobalError(null);
            $this->parameters->setMailingValidationErrors(array());

            return $this->redirect('/mailing');
        }

        $mailingSettings->setTransportOptions($postSettings);
        $errors = $mailingSettings->validate();
        $this->parameters->setMailingValidationErrors($errors);

        if (count($errors) > 0) {
            return $this->redirect('/mailing');
        }

        $checker = new MailingChecker($mailingSettings);

        if (true !== $status = $checker->testTransport()) {
            $this->parameters->setMailingGlobalError($status);

            return $this->redirect('/mailing');
        }

        $this->parameters->setMailingGlobalError(null);

        return $this->redirect('/install');
    }

    public function skipMailingStep()
    {
        $this->parameters->reinitializeMailingSettings();
        $this->parameters->setMailingGlobalError(null);
        $this->parameters->setMailingValidationErrors(array());

        return $this->redirect('/install');
    }

    public function installStep()
    {
        return $this->renderStep('install.html.php', 'installation', array());
    }

    public function installSubmitStep()
    {
        $sendDatasConfirmed = isset($_POST['sendData']) && $_POST['sendData'] === 'true';
        $token = $this->generateToken(20);
        $this->parameters->setToken($token);

        if ($sendDatasConfirmed) {
            $this->parameters->setHasConfirmedSendDatas(true);
            $datas = $this->generateSentDatas();
        }
        $this->container->getWriter()->writeParameters($this->container->getParameterBag());
        $installer = $this->container->getInstaller();
        session_write_close(); // needed because symfony will init a new session
        $installer->install();
        $this->request->getSession()->invalidate();

        if (!$installer->hasSucceeded()) {
            return $this->redirect('/error/'.$installer->getLogFilename());
        }

        if ($sendDatasConfirmed) {
            $this->sendDatas($datas);
        }

        return $this->redirect('/../app.php');
    }

    public function installStatusStep($timestamp = null)
    {
        $logDir = $this->container->getAppDirectory().'/logs';
        $logFile = null;

        if (!$timestamp) {
            $newestFileTime = 0;

            foreach (new \DirectoryIterator($logDir) as $item) {
                if ($item->isFile()
                    && preg_match('/^install\-(\d+)\.log$/', $item->getFilename(), $matches)
                    && $item->getMTime() > $newestFileTime) {
                    $newestFileTime = $item->getMTime();
                    $logFile = $item->getPathname();
                    $timestamp = $matches[1];
                }
            }
        } else {
            $logFile = $logDir.'/install-'.$timestamp.'.log';
        }

        return new JsonResponse(
            array(
                'timestamp' => $timestamp,
                'content' => file_get_contents($logFile),
            )
        );
    }

    public function failedInstallStep($logFilename)
    {
        $logFile = $this->container->getAppDirectory().'/logs/'.$logFilename;
        $logContent = file_exists($logFile) ? file_get_contents($logFile) : null;

        return $this->renderStep(
            'error.html.php',
            'failed_install',
            array(
                'log' => $logContent,
                'log_filename' => $logFilename,
            )
        );
    }

    private function renderStep($template, $titleKey, array $variables)
    {
        return new Response(
            $this->container->getTemplateEngine()->render(
                'layout.html.php',
                array(
                    'stepTitle' => $titleKey,
                    'stepTemplate' => $template,
                    'stepVariables' => $variables,
                )
            )
        );
    }

    private function redirect($path)
    {
        $path = $path === '/' ? '' : $path;

        return new RedirectResponse($this->request->getBaseUrl().$path);
    }

    private function getTransportId($transport)
    {
        switch ($transport) {
            case 'Sendmail / Postfix':
                return 'sendmail';
            case 'SMTP':
            case 'Gmail':
            default:
                return strtolower($transport);
        }
    }

    private function generateSentDatas()
    {
        $datas = array();
        $platformSettings = $this->parameters->getPlatformSettings();

        $datas['url'] = $this->configHandler->getParameter('datas_sending_url');
        $datas['ip'] = $this->container->getClientIp();
        $datas['name'] = $platformSettings->getName();
        $datas['lang'] = $platformSettings->getLanguage();
        $datas['country'] = $this->parameters->getCountry();
        $datas['supportEmail'] = $platformSettings->getSupportEmail();
        $datas['version'] = $this->container->getVersion();
        $datas['nbWorkspaces'] = 0;
        $datas['nbPersonalWorkspaces'] = 0;
        $datas['nbUsers'] = 0;
        $datas['type'] = 0;
        $datas['token'] = $this->parameters->getToken();

        $currentUrl = $this->request->getHttpHost().
            $this->request->getRequestUri();
        $datas['platformUrl'] = str_replace('install.php/install', 'app.php', $currentUrl);

        return $datas;
    }

    private function sendDatas(array $datas)
    {
        $url = $datas['url'];
        $ip = $datas['ip'];
        $name = $datas['name'];
        $platformUrl = $datas['platformUrl'];
        $lang = $datas['lang'];
        $country = $datas['country'];
        $supportEmail = $datas['supportEmail'];
        $version = $datas['version'];
        $nbWorkspaces = $datas['nbWorkspaces'];
        $nbPersonalWorkspaces = $datas['nbPersonalWorkspaces'];
        $nbUsers = $datas['nbUsers'];
        $type = $datas['type'];
        $token = $datas['token'];
        $this->configHandler->setParameter('platform_url', $platformUrl);

        $postDatas = "ip=$ip".
            "&name=$name".
            "&url=$platformUrl".
            "&lang=$lang".
            "&country=$country".
            "&email=$supportEmail".
            "&version=$version".
            "&workspaces=$nbWorkspaces".
            "&personal_workspaces=$nbPersonalWorkspaces".
            "&users=$nbUsers".
            "&stats_type=$type".
            "&token=$token";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postDatas);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
    }

    private function generateToken($length)
    {
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $charsSize = count($chars);
        $token = uniqid();

        while (strlen($token) < $length) {
            $index = rand(0, $charsSize - 1);
            $token .= $chars[$index];
        }

        return $token;
    }
}
