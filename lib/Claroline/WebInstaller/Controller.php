<?php

namespace Claroline\WebInstaller;

use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Claroline\CoreBundle\Library\Installation\Settings\DatabaseSettings;

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

    public function databaseStep(array $formData = array(), $errors = array())
    {
        $session = $this->request->getSession();
        $formData = $formData ?: $session->get('database_settings', array());
        $errors = $errors ?: $session->get('database_settings_errors', array());

        return $this->renderStep(
            'database',
            array_merge($formData, array('errors' => $errors))
        );
    }

    public function databaseStepSubmit()
    {
        $settings = $this->request->request->all();

        if (isset($settings['database_driver'])) {
            switch ($settings['database_driver']) {
                case 'PostgreSQL':
                    $settings['database_driver'] = 'pdo_pgsql';
                    break;
                case 'MySQL':
                default:
                    $settings['database_driver'] = 'pdo_mysql';
            }
        }

        $checker = new DatabaseSettings($settings);
        $errors = $checker->validate();
        $this->request->getSession()->set('database_settings', $settings);
        $this->request->getSession()->set('database_settings_errors', $errors);

        if (count($errors) > 0) {
            return $this->databaseStep($settings, $errors);
        } elseif ($checker->canConnect()) {
            return $this->adminUserStep();
        } else {
            echo 'UNABLE TO CONNECT/CREATE DB';
        }
    }

    private function adminUserStep()
    {
        echo 'OK -> admin user';
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
