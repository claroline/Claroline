<?php

namespace Claroline\WebInstaller;

class Container
{
    private $rootDirectory;
    private $baseUrl;
    private $cachedServices = array();

    public function __construct($rootDirectory, $baseUrl)
    {
        $this->rootDirectory = $rootDirectory;
        $this->baseUrl = $baseUrl;
        $this->cachedServices = array();
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getTranslator()
    {
        if (!isset($this->cachedServices['translator'])) {
            $this->cachedServices['translator'] = new Translator($this->rootDirectory . '/translations', 'en', 'en');
        }

        return $this->cachedServices['translator'];
    }

    public function getTemplateEngine()
    {
        if (!isset($this->cachedServices['templating'])) {
            $templating = new TemplateEngine($this->rootDirectory . '/templates');
            $baseUrl = $this->baseUrl;
            $templating->addHelpers(
                array(
                    'trans' => $this->getTranslator()->toClosure(),
                    'path' => function ($path) use ($baseUrl) {
                        return rtrim($baseUrl . $path, '/');
                    }
                )
            );
            $this->cachedServices['templating'] = $templating;
        }

        return $this->cachedServices['templating'];
    }
}
