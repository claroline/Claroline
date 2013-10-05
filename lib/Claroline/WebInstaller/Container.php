<?php

namespace Claroline\WebInstaller;

use Symfony\Component\HttpFoundation\Request;

class Container
{
    private $request;
    private $rootDirectory;
    private $cachedServices = array();

    public function __construct(Request $request, $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
        $this->request = $request;
        $this->cachedServices = array();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getTranslator()
    {
        if (!isset($this->cachedServices['translator'])) {
            $this->cachedServices['translator'] = new Translator(
                $this->rootDirectory . '/translations', 'en', 'en'
            );
        }

        $this->cachedServices['translator']->setLanguage(
            $this->request->getSession()->get('language', 'en')
        );

        return $this->cachedServices['translator'];
    }

    public function getTemplateEngine()
    {
        if (!isset($this->cachedServices['templating'])) {
            $templating = new TemplateEngine($this->rootDirectory . '/templates');
            $baseUrl = $this->request->getBaseUrl();
            $templating->addHelpers(
                array(
                    'trans' => $this->getTranslator()->toClosure(),
                    'path' => function ($path) use ($baseUrl) {
                        return rtrim($baseUrl . $path, '/');
                    },
                    'value' => function (array $search, $key) {
                        if (isset($search[$key])) {
                            return $search[$key];
                        }
                    }
                )
            );
            $this->cachedServices['templating'] = $templating;
        }

        return $this->cachedServices['templating'];
    }
}
