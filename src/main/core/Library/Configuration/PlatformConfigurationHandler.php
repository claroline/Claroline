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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service used for accessing or modifying the platform configuration parameters.
 */
class PlatformConfigurationHandler
{
    /** @var string */
    private $configFile;
    /** @var RequestStack */
    private $requestStack;
    /** @var array */
    private $parameters = [];
    /** @var array */
    private $defaultConfigs = [];
    /** @var string[] */
    private $mapping = [];

    public function __construct(string $configFile, RequestStack $requestStack)
    {
        $this->configFile = $configFile;
        $this->requestStack = $requestStack;
        if (file_exists($this->configFile)) {
            $this->parameters = json_decode(file_get_contents($this->configFile), true);
        }
    }

    public function hasParameter(string $parameter): bool
    {
        return ArrayUtils::has($this->parameters, $parameter);
    }

    /**
     * @param string $parameter  - The path of the parameter inside the parameters array
     * @param bool   $fromDomain - Get the parameter value for the client IP domain (return the platform one if no override)
     *
     * @return mixed|null
     */
    public function getParameter(string $parameter, bool $fromDomain = true)
    {
        if ($fromDomain) {
            $request = $this->requestStack->getMasterRequest();

            // check if there is a custom configuration for the current request ip
            $ip = $request ? $request->getClientIp() : null;
            $forwarded = $request ? $request->server->get('X-Forwarded-For') : null; // I can only get trusted proxies if I use symfony getClientIps()
            $domains = ArrayUtils::get($this->parameters, 'domains');
            if (!empty($domains) && $ip) {
                $callerDomain = null;
                foreach ($domains as $domain) {
                    if ((empty($domain['ips']) || in_array($ip, $domain['ips'])) && (empty($domain['xForwardedFor']) || in_array($forwarded, $domain['xForwardedFor']))) {
                        $callerDomain = $domain;
                        break;
                    }
                }

                // check if there is a custom configuration for this domain
                if ($callerDomain && !empty($callerDomain['config']) && ArrayUtils::has($callerDomain['config'], $parameter)) {
                    $value = ArrayUtils::get($callerDomain['config'], $parameter);
                    if (is_array($value) && ArrayUtils::isAssociative($value)) {
                        // merge the domain param with the default one in order to get the full value
                        // even if the domain does not override the full array
                        $appValue = $this->getParameter($parameter, false);

                        return array_replace_recursive($appValue, $value);
                    }

                    return $value;
                }
            }
        }

        // parameter is defined
        if ($this->hasParameter($parameter)) {
            return ArrayUtils::get($this->parameters, $parameter);
        }

        // parameter uses an old format
        if (array_key_exists($parameter, $this->mapping) && ArrayUtils::has($this->parameters, $this->mapping[$parameter])) {
            return ArrayUtils::get($this->parameters, $this->mapping[$parameter]);
        }

        return null;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameter(string $parameter, $value)
    {
        if (!is_writable($this->configFile)) {
            throw new \RuntimeException('Platform options is not writable');
        }

        ArrayUtils::set($this->parameters, $parameter, $value);

        $this->saveParameters();
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = array_replace_recursive($this->parameters, $parameters);

        $this->saveParameters();
    }

    public function addDefaultParameters(ParameterProviderInterface $config)
    {
        $newDefault = $config->getDefaultParameters();
        $newDefaultClass = get_class($config);

        //check if the parameter already exists to avoid overriding stuff by mistake
        foreach ($this->defaultConfigs as $defaultConfig) {
            $duplicates = array_intersect_key($defaultConfig, $newDefault);

            if (count($duplicates) > 0) {
                throw new \RuntimeException("The following duplicate key(s) were found in the {$newDefaultClass} configuration file: ".implode(', ', array_keys($duplicates)));
            }
        }

        $this->defaultConfigs[$newDefaultClass] = $newDefault;
        $this->parameters = array_replace_recursive($newDefault, $this->parameters);
    }

    public function addLegacyMapping(LegacyParametersMappingInterface $mapping)
    {
        $this->mapping = array_merge($this->mapping, $mapping->getMapping());
    }

    public function saveParameters()
    {
        ksort($this->parameters);
        file_put_contents($this->configFile, json_encode($this->parameters, JSON_PRETTY_PRINT));
    }
}
