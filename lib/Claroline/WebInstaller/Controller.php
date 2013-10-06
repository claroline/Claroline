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
    }

    public function languageStep()
    {
        return $this->renderStep(
            'language',
            array('language' => $this->container->getTranslator()->getLanguage())
        );
    }

    public function languageStepSubmit()
    {
        switch ($this->request->request->get('language')) {
            case 'Français':
                $language = 'fr';
                break;
            case 'English':
            default:
                $language = 'en';
        }

        $this->request->getSession()->set('language', $language);
        $this->container->getTranslator()->setLanguage($language);

        return $this->languageStep();
    }

    public function requirementStep()
    {
        $settingChecker = new SettingChecker();

        return $this->renderStep(
            'requirements',
            array(
                'setting_categories' => $settingChecker->getSettingCategories(),
                'has_failed_recommendation' => $settingChecker->hasFailedRecommendation(),
                'has_failed_requirement' => $settingChecker->hasFailedRequirement()
            )
        );
    }

    public function databaseStep()
    {
        $session = $this->request->getSession();
        $formData = $session->get('database_settings', array());
        $formErrors = $session->get('database_settings_errors', array());
        $globalError = $session->get('database_global_error', false);

        return $this->renderStep(
            'database',
            array_merge(
                $formData,
                array(
                    'global_error' => $globalError,
                    'form_errors' => $formErrors
                )
            )
        );
    }

    public function databaseStepSubmit()
    {
        $settings = $this->request->request->all();

        if (isset($settings['driver'])) {
            switch ($settings['driver']) {
                case 'PostgreSQL':
                    $settings['driver'] = 'pdo_pgsql';
                    break;
                case 'MySQL':
                default:
                    $settings['driver'] = 'pdo_mysql';
            }
        }

        $session = $this->request->getSession();
        $session->set('database_settings', $settings);
        $checker = new DatabaseChecker($settings);

        if (count($errors = $checker->getValidationErrors()) > 0) {
            $session->set('database_settings_errors', $errors);
            $session->remove('database_global_error');

            return $this->databaseStep();
        } elseif (true !== $status = $checker->connectToDatabase()) {
            $session->remove('database_settings_errors');
            $session->set('database_global_error', $status);

            return $this->databaseStep();
        }

        $session->remove('database_global_error');
        $session->remove('database_settings_errors');

        return $this->adminUserStep();
    }

    private function adminUserStep()
    {
        return new Response('Admin creation');
    }

    private function renderStep($template, array $variables)
    {
        return new Response(
            $this->container->getTemplateEngine()->render(
                'layout.php',
                array(
                    'stepTemplate' => $template . '.php',
                    'stepVariables' => $variables
                )
            )
        );
    }
}
