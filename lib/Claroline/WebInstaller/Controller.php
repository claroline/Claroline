<?php

namespace Claroline\WebInstaller;

use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Claroline\CoreBundle\Library\Installation\Settings\DatabaseChecker;

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
            array('install_language' => $this->parameters->getInstallationLanguage())
        );
    }

    public function languageStepSubmit()
    {
        $language = $this->request->request->get('install_language');
        $languageCode = $this->getLanguageCode($language);
        $this->parameters->setInstallationLanguage($languageCode);
        $this->container->getTranslator()->setLanguage($languageCode);

        return $this->languageStep();
    }

    public function requirementStep()
    {
        $settingChecker = new SettingChecker();

        return $this->renderStep(
            'requirements.html.php',
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
            return $this->databaseStep();
        }

        $checker = new DatabaseChecker($databaseSettings);

        if (true !== $status = $checker->connectToDatabase()) {
            $this->parameters->setDatabaseGlobalError($status);

            return $this->databaseStep();
        }

        $this->parameters->setDatabaseGlobalError(null);

        return $this->platformStep();
    }

    public function platformStep()
    {
        $platformSettings = $this->parameters->getPlatformSettings();

        if (!$platformSettings->getLanguage()) {
            $platformSettings->setLanguage($this->parameters->getInstallationLanguage());
        }

        return $this->renderStep(
            'platform.html.php',
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
            return $this->platformStep();
        }

        return $this->adminUserStep();
    }

    private function adminUserStep()
    {
        return new Response('Admin user step');
    }

    private function renderStep($template, array $variables)
    {
        return new Response(
            $this->container->getTemplateEngine()->render(
                'layout.html.php',
                array(
                    'stepTemplate' => $template,
                    'stepVariables' => $variables
                )
            )
        );
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
}
