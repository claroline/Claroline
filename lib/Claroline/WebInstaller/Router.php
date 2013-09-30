<?php

namespace Claroline\WebInstaller;

class Router
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch($pathInfo, $method)
    {
        $controller = new Controller($this->container);

        if ($pathInfo === '/' && $method === 'GET') {
            return $controller->languageStep();
        } elseif ($pathInfo === '/' && $method === 'POST') {
            return $controller->languageStepSubmit();
        } elseif ($pathInfo === '/requirements' && $method = 'GET') {
            return $controller->requirementStep();
        }

        header("HTTP/1.0 404 Not Found");
        echo '<h1>Page not found</h1>';
    }
}
