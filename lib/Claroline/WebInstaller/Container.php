<?php

namespace Claroline\WebInstaller;

use Symfony\Component\HttpFoundation\Request;

class Container
{
    private $request;
    private $appDirectory;
    private $installerDirectory;
    private $cachedServices = array();

    public function __construct(Request $request, $appDirectory)
    {
        $this->request = $request;
        $this->appDirectory = $appDirectory;
        $this->installerDirectory = __DIR__ . '/../../..';
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
                $this->installerDirectory . '/translations', 'en', 'en'
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
            $templating = new TemplateEngine($this->installerDirectory . '/templates');
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

    public function getWriter()
    {
        return new Writer(
            $this->appDirectory . '/config/parameters.yml.dist',
            $this->appDirectory . '/config/parameters.yml',
            $this->appDirectory . '/config/platform_options.yml',
            $this->appDirectory . '/config/is_installed.php'
        );
    }

    public function getInstaller()
    {
        return new Installer(
            $this->getParameterBag()->getFirstAdminSettings(),
            $this->getWriter(),
            $this->appDirectory . '/AppKernel.php',
            'AppKernel'
        );
    }
}
