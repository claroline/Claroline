<?php

namespace Claroline\WebInstaller;

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
        $_SESSION['language'] = $_POST['language'];
        $this->container->getTranslator()->setLanguage($_SESSION['language']);
        $this->languageStep();
    }

    public function requirementStep()
    {
        $this->displayStep('requirements', array('no_next' => true));
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
