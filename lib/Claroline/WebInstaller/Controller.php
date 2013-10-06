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
        $checker = new DatabaseChecker($databaseSettings);
        $errors = $checker->validateSettings();
        $this->parameters->setDatabaseValidationErrors($errors);

        if (count($errors) > 0) {
            return $this->databaseStep();
        } elseif (true !== $status = $checker->connectToDatabase()) {
            $this->parameters->setDatabaseGlobalError($status);

            return $this->databaseStep();
        } else {
            $this->parameters->setDatabaseGlobalError(null);
        }

        return $this->platformStep();
    }

    public function platformStep()
    {
        if (!$this->parameters->getPlatformLanguage()) {
            $this->parameters->setPlatformLanguage($this->parameters->getInstallationLanguage());
        }

        return $this->renderStep(
            'platform.html.php',
            array(
                'platform_language' => $this->parameters->getPlatformLanguage()
            )
        );
    }

    public function platformSubmitStep()
    {
        $this->parameters->setPlatformLanguage(
            $this->getLanguageCode($this->request->request->get('platform_language'))
        );

        return new Response('platform submit');
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
