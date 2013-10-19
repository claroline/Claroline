<?php

namespace Claroline\WebInstaller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Claroline\CoreBundle\Library\Installation\Settings\DatabaseChecker;
use Claroline\CoreBundle\Library\Installation\Settings\MailingChecker;

class Controller
{
    private $container;
    private $request;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request = $this->container->getRequest();
        $this->parameters = $this->container->getParameterBag();
    }

    public function languageStep()
    {
        return $this->renderStep(
            'language.html.php',
            'welcome',
            array('install_language' => $this->parameters->getInstallationLanguage())
        );
    }

    public function languageStepSubmit()
    {
        $language = $this->request->request->get('install_language');
        $languageCode = $this->getLanguageCode($language);
        $this->parameters->setInstallationLanguage($languageCode);
        $this->container->getTranslator()->setLanguage($languageCode);

        return $this->redirect('/');
    }

    public function requirementStep()
    {
        $settingChecker = new SettingChecker();

        return $this->renderStep(
            'requirements.html.php',
            'requirements_check',
            array(
                'setting_categories' => $settingChecker->getSettingCategories(),
                'has_failed_recommendation' => $settingChecker->hasFailedRecommendation(),
                'has_failed_requirement' => $settingChecker->hasFailedRequirement()
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
                'validation_errors' => $this->parameters->getDatabaseValidationErrors()
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

        if (true !== $status = $checker->connectToDatabase()) {
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
                'errors' => $this->parameters->getPlatformValidationErrors()
            )
        );
    }

    public function platformSubmitStep()
    {
        $postSettings = $this->request->request->all();
        $postSettings['language'] = $this->getLanguageCode($postSettings['language']);
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
                'errors' => $this->parameters->getFirstAdminValidationErrors()
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
                'validation_errors' => $this->parameters->getMailingValidationErrors()
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
        $this->container->getWriter()->writeParameters($this->container->getParameterBag());
        $installer = $this->container->getInstaller();
        session_write_close(); // needed because symfony will init a new session
        ini_set('max_execution_time', 180);
        $installer->install();
        $this->request->getSession()->invalidate();

        return $this->redirect('/../app.php');
    }
    private function renderStep($template, $titleKey, array $variables)
    {
        return new Response(
            $this->container->getTemplateEngine()->render(
                'layout.html.php',
                array(
                    'stepTitle' => $titleKey,
                    'stepTemplate' => $template,
                    'stepVariables' => $variables
                )
            )
        );
    }

    private function redirect($path)
    {
        $path = $path === '/' ? '' : $path;

        return new RedirectResponse($this->request->getBaseUrl() . $path);
    }

    private function getLanguageCode($language)
    {
        switch ($language) {
            case 'Français':
                return 'fr';
            case 'English':
            default:
                return 'en';
        }
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
}
