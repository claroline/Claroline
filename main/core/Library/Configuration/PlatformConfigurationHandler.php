<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *A
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Configuration;

use Claroline\AppBundle\API\Utils\ArrayUtils;

/**
 * Service used for accessing or modifying the platform configuration parameters.
 */
class PlatformConfigurationHandler
{
    private $configFile;
    private $parameters;

    /**
     * PlatformConfigurationHandler constructor.
     *
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $this->parameters = [];
        $this->defaultConfigs = [];
        $this->configFile = $configFile;
        $this->parameters = $this->mergeParameters();
        //just in case init went wrong
        $mapping = new LegacyParametersMapping();
        $this->mapping = $mapping->getMapping();
    }

    public function hasParameter($parameter)
    {
        return ArrayUtils::has($this->parameters, $parameter);
    }

    /**
     * @param string $parameter
     *
     * @return mixed
     */
    public function getParameter($parameter)
    {
        if ($this->hasParameter($parameter)) {
            return ArrayUtils::get($this->parameters, $parameter);
        }

        if (array_key_exists($parameter, $this->mapping) && ArrayUtils::has($this->parameters, $this->mapping[$parameter])) {
            return ArrayUtils::get($this->parameters, $this->mapping[$parameter]);
        }

        //otherwise let's go default
        $defaults = [];
        foreach ($this->defaultConfigs as $default) {
            $defaults = array_merge($default, $defaults);
        }

        if (array_key_exists($parameter, $defaults)) {
            return ArrayUtils::get($defaults, $parameter);
        }

        if (array_key_exists($parameter, $this->mapping) && ArrayUtils::has($defaults, $this->mapping[$parameter])) {
            return ArrayUtils::get($defaults, $this->mapping[$parameter]);
        }

        return null;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function addLegacyMapping(LegacyParametersMappingInterface $mapping)
    {
        $this->mapping = array_merge($this->mapping, $mapping->getMapping());
    }

    public function getDefaultsConfigs()
    {
        return $this->defaultConfigs;
    }

    public function setParameter($parameter, $value)
    {
        if (!is_writable($this->configFile)) {
            throw new \RuntimeException('Platform options is not writable');
        }
        $this->parameters[$parameter] = $value;

        ksort($this->parameters);
        $parameters = json_encode($this->parameters, JSON_PRETTY_PRINT);
        file_put_contents($this->configFile, $parameters);
    }

    public function isRedirectOption($option)
    {
        return $this->getParameter('authentication.redirect_after_login_option') === $option;
    }

    public function addDefaultParameters(ParameterProviderInterface $config)
    {
        $newDefault = $config->getDefaultParameters();
        $newDefaultClass = get_class($config);

        //check if the parameter already exists to avoid overriding stuff by mistake
        foreach ($this->defaultConfigs as $defaultConfig) {
            $duplicates = array_intersect_key($defaultConfig, $newDefault);

            if (count($duplicates) > 0) {
                throw new \RuntimeException(
                    "The following duplicate key(s) were found in the {$newDefaultClass} configuration file: ".implode(', ', array_keys($duplicates))
                );
            }
        }

        $this->defaultConfigs[$newDefaultClass] = $newDefault;
        $this->parameters = array_merge($newDefault, $this->parameters);
    }

    protected function mergeParameters()
    {
        $defaults = new PlatformDefaults();

        if (!file_exists($this->configFile)) {
            file_put_contents($this->configFile, json_encode($defaults->getDefaultParameters(), JSON_PRETTY_PRINT));
        }

        $parameters = json_decode(file_get_contents($this->configFile), true);

        if ($parameters) {
            return $this->arrayMerge($parameters, $defaults->getDefaultParameters());
        }

        return $this->parameters;
    }

    public function getDefaultParameters()
    {
        return $this->parameters;
    }

    public function arrayMerge(array $array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1) && !in_array($value, $array1)) {
                $array1[$key] = $value;
            } else {
                if (is_array($value)) {
                    if (array_key_exists($key, $array1)) {
                        $array1[$key] = $this->arrayMerge($array1[$key], $array2[$key]);
                    } else {
                        $array1[$key] = $value;
                    }
                }
            }
        }

        return $array1;
    }
}
