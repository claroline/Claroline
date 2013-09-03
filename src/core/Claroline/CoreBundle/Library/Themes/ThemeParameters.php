<?php

namespace Claroline\CoreBundle\Library\Themes;

class ThemeParameters
{
    private $colors;
    private $parameters;

    public function __construct($file = null)
    {
        $path = __DIR__.'/parameters/';

        $this->parameters = $this->getParsedFiles($path);

        if (file_exists($file)) {

            $variables = $this->parseFile($file);

            foreach ($this->parameters as $name => $parameters) {

                foreach ($parameters as $code => $value) {

                    if ($value and isset($variables[$code])) {
                        $this->parameters[$name][$code] = $variables[$code];
                    }
                }
            }
        }
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

    public function getParsedFiles($path)
    {
        $parameters = array();

        $files = scandir($path);

        foreach ($files as $file) {
            if (pathinfo($path.$file, PATHINFO_EXTENSION) == "less") {
                $parameters[substr(pathinfo($file, PATHINFO_FILENAME), 3)] = $this->parseFile($path.$file);
            }
        }

        return $parameters;
    }
}
