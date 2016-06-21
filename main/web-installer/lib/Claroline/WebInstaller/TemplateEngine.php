<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $helpers = $this->helpers;
        $render = function ($template, array $variables = array()) use ($dir, $helpers, &$render) {
            $var = function ($name, $default = null) use ($variables) {
                if (array_key_exists($name, $variables)) {
                    return $variables[$name];
                }

                if (func_num_args() > 1) {
                    return func_get_arg(1);
                }

                throw new \Exception("Unknown variable '{$name}'");
            };

            foreach ($helpers as $name => $helper) {
                $$name = $helper;
            }

            ob_start();
            require $dir.'/'.$template;

            return ob_get_clean();
        };

        return $render($template, $variables);
    }
}
