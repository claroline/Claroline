<?php

namespace Claroline\WebInstaller;

use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;

class Controller
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function languageStep()
    {
        $this->displayStep(
            'language',
            array('language' => $this->container->getTranslator()->getLanguage())
        );
    }

    public function languageStepSubmit()
    {
        switch ($_POST['language']) {
            case 'Français':
                $language = 'fr';
                break;
            case 'English':
            default:
                $language = 'en';
        }

        $_SESSION['language'] = $language;
        $this->container->getTranslator()->setLanguage($language);
        $this->languageStep();
    }

    public function requirementStep()
    {
        $settingChecker = new SettingChecker();
        $this->displayStep(
            'requirements',
            array(
                'setting_categories' => $settingChecker->getSettingCategories(),
                'has_failed_requirement' => $settingChecker->hasFailedRequirement()
            )
        );
    }

    public function databaseStep()
    {
        $this->displayStep(
            'requirements',
            array()
        );
    }

    private function displayStep($template, array $variables)
    {
        echo $this->container->getTemplateEngine()->render(
            'layout.php',
            array(
                'stepTemplate' => $template . '.php',
                'stepVariables' => $variables
            )
        );
    }

    private function redirect($path)
    {
        header('Location: ' . $this->container->getBaseUrl() . $path);
    }
}
