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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.config.platform_config_handler")
 *
 * Service used for accessing or modifying the platform configuration parameters.
 */
class PlatformConfigurationHandler
{
    private $configFile;
    private $parameters;
    private $lockedParameters;

    /**
     * PlatformConfigurationHandler constructor.
     *
     * @DI\InjectParams({
     *     "configFile" = @DI\Inject("%claroline.param.platform_options%")
     * })
     *
     * @param string $configFile
     * @param string $lockedConfigFile
     */
    public function __construct($configFile)
    {
        $this->parameters = [];
        $this->defaultConfigs = [];
        $this->configFile = $configFile;
        $this->parameters = $this->mergeParameters();
        $this->arrayUtils = new ArrayUtils();
    }

    public function hasParameter($parameter)
    {
        return $this->arrayUtils->has($this->parameters, $parameter);
    }

    /**
     * @param string $parameter
     *
     * @deprecated (use ParameterSerializer instead)
     *
     * @return mixed
     */
    public function getParameter($parameter)
    {
        if ($this->hasParameter($parameter)) {
            return $this->arrayUtils->get($this->parameters, $parameter);
        }

        $mapping = new LegacyParametersMapping();
        $legacyMapping = $mapping->getMapping();

        if (array_key_exists($parameter, $legacyMapping)) {
            return $this->arrayUtils->get($this->parameters, $legacyMapping[$parameter]);
        }

        return null;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getDefaultsConfigs()
    {
        return $this->defaultConfigs;
    }

    public function setParameter($parameter, $value)
    {
        throw new \Exception('use serializer instead');
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
        if (!file_exists($this->configFile)) {
            $defaults = new PlatformDefaults();
            file_put_contents($this->configFile, json_encode($defaults->getDefaultParameters(), JSON_PRETTY_PRINT));
        }

        $parameters = json_decode(file_get_contents($this->configFile), true);

        if ($parameters) {
            return array_merge($this->parameters, $parameters);
        }

        return $this->parameters;
    }

    public function getDefaultParameters()
    {
        return $this->parameters;
    }
}
