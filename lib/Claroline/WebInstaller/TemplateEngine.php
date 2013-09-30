<?php

namespace Claroline\WebInstaller;

class TemplateEngine
{
    private $templateDirectory;
    private $helpers;

    public function __construct($templateDirectory)
    {
        $this->templateDirectory = $templateDirectory;
        $this->helpers = array();
    }

    public function addHelpers(array $helpers)
    {
        foreach ($helpers as $name => $helper) {
            if (isset($this->helpers[$name]) || $name === 'render' || $name === 'var') {
                throw new \Exception("A helper named '{$name}' is already registered");
            }

            if (!$helper instanceof \Closure) {
                throw new \Exception("Helper '{$name}' is not a closure");
            }

            $this->helpers[$name] = $helper;
        }
    }

    public function render($template, array $variables = array())
    {
        $dir = $this->templateDirectory;
        $render = function ($template, array $variables = array()) use ($dir, &$render) {
            $var = function ($name) use ($variables) {
                if (isset($variables[$name])) {
                    return $variables[$name];
                }

                throw new \Exception("Unknown variable '{$name}'");
            };

            foreach ($this->helpers as $name => $helper) {
                $$name = $helper;
            }

            ob_start();
            require $dir . '/' . $template;

            return ob_get_clean();
        };

        return $render($template, $variables);
    }
}
