<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Configuration;

class PlatformConfiguration
{
    public function __construct($parameters)
    {
        foreach ($parameters as $parameter => $value) {
            $varName = $this->toCamelCase($parameter);
            $this->{$varName} = $value;
        }
    }

    public function getParameters()
    {
        $parameters = [];
        $properties = get_object_vars($this);

        foreach ($properties as $property => $value) {
            $parameters[$this->toUnderscore($property)] = $value;
        }

        return $parameters;
    }

    //@see http://stackoverflow.com/questions/1589468/convert-camelcase-to-under-score-case-in-php-autoload
    public function toUnderscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    public function toCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    public function __call($name, $parameters)
    {
        if (strpos($name, 'get') === 0) {
            $property = lcfirst(str_replace('get', '', $name));
            if (property_exists($this, $property)) {
                return $this->$property;
            } else {
                throw new \RuntimeException("Property {$property} doesn't exist in the configuration file.");
            }
        } else {
            if (strpos($name, 'set') === 0) {
                $property = lcfirst(str_replace('set', '', $name));

                if (property_exists($this, $property)) {
                    $this->$property = $parameters[0];

                    return;
                } else {
                    throw new \RuntimeException("Property {$property} doesn't exist in the configuration file.");
                }
            }
        }

        throw new \RuntimeException("The function {$name} doesn't exist.");
    }

    public function __get($key)
    {
        $property = $this->toCamelCase($key);

        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new \RuntimeException("Property {$property} doesn't exist in the configuration file.");
        }
    }

    public function __set($key, $value)
    {
        $property = $this->toCamelCase($key);
        $this->$property = $value;
    }
}
