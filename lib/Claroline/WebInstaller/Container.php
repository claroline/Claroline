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

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ParameterBag
     */
    public function getParameterBag()
    {
        $session = $this->request->getSession();

        if (!$session->has('parameter_bag')) {
            $session->set('parameter_bag', new ParameterBag());
        }

        return $session->get('parameter_bag');
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        if (!isset($this->cachedServices['translator'])) {
            $this->cachedServices['translator'] = new Translator(
                $this->rootDirectory . '/translations', 'en', 'en'
            );
        }

        $this->cachedServices['translator']->setLanguage(
            $this->getParameterBag()->getInstallationLanguage()
        );

        return $this->cachedServices['translator'];
    }

    /**
     * @return TemplateEngine
     */
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
