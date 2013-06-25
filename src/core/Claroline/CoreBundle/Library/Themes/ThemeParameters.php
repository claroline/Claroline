<?php

namespace Claroline\CoreBundle\Library\Themes;

class ThemeParameters
{
    private $colors;
    private $parameters;

    public function __construct($file = null)
    {
        $path = __DIR__.'/parameters/';

        $this->colors = $this->parseFile($path.'colors.less');

        $this->parameters = array(
            'Scaffolding' => $this->parseFile($path.'scaffolding.less'),
            'Links' => $this->parseFile($path.'links.less'),
            'Typography' => $this->parseFile($path.'typography.less'),
            'Sizing' => $this->parseFile($path.'sizing.less'),
            'Tables' => $this->parseFile($path.'tables.less'),
            'Buttons' => $this->parseFile($path.'buttons.less'),
            'Forms' => $this->parseFile($path.'forms.less'),
            'Dropdowns' => $this->parseFile($path.'dropdowns.less'),
            'Components' => $this->parseFile($path.'components.less'),
            'Navbar' => $this->parseFile($path.'navbar.less'),
            'Inverted Navbar' => $this->parseFile($path.'invertednavbar.less'),
            'Pagination' => $this->parseFile($path.'pagination.less'),
            'Hero Unit' => $this->parseFile($path.'herounit.less'),
            'Alerts' => $this->parseFile($path.'alerts.less'),
            'Tooltip & Popovers' => $this->parseFile($path.'popovers.less')
        );

        if (file_exists($file)) {

            $variables = $this->parseFile($file);

            foreach ($this->colors as $code => $value) {
                if (isset($variables[$code])) {
                    $this->colors[$code] = $variables[$code];
                }
            }

            foreach ($this->parameters as $name => $parameters) {

                foreach ($parameters as $code => $value) {
                    if (isset($variables[$code])) {
                        $this->parameters[$name][$code] = $variables[$code];
                    }
                }
            }
        }
    }

    public function getColors()
    {
        return $this->colors;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function parseFile($path)
    {
        $parameters = array();
        $lines = file($path);

        foreach ($lines as $line) {

            $line = explode(':', $line);

            if (count($line) > 1) {

                $code = trim($line[0]);
                $value = array_pop($line);

                if (is_array($value)) {
                    $value = implode(':', $value); //parameter may contain : character
                }

                $parameters[$code] = trim(str_replace(';', '', $value));
            }
        }

        return $parameters;
    }
}
