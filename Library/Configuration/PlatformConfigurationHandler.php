<?php

namespace Claroline\CoreBundle\Library\Configuration;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Configuration\PlatformConfiguration;

/**
 * Service used for accessing or modifying the platform configuration parameters in prod/dev
 * environments. The service annotation cannot be used as the class is not the same in test
 * environment (see Library\Testing\PlatformTestConfigurationHandler).
 */
class PlatformConfigurationHandler
{
    private $configFile;
    private $options = array(
        'name' => null,
        'footer' => null,
        'logo' => 'clarolineconnect.png',
        'allow_self_registration' => true,
        'locale_language' => 'fr',
        'theme' => 'claroline'
    );

    public function __construct(array $configFiles)
    {
        $this->configFile = $configFiles['prod'];
        $this->options = array_merge($this->options, Yaml::parse($this->configFile));
    }

    public function hasParameter($parameter)
    {
        if (array_key_exists($parameter, $this->options)) {
            return true;
        }

        return false;
    }

    public function getParameter($parameter)
    {
        $this->checkParameter($parameter);

        return $this->options[$parameter];
    }

    public function setParameter($parameter, $value)
    {
        if (!is_writable($this->configFile)) {
            $exception = new UnwritableException();
            $exception->setPath($this->configFile);

            throw $exception;
        }

        $this->checkParameter($parameter);
        $this->options[$parameter] = $value;
        file_put_contents($this->configFile, Yaml::dump($this->options));
    }

    public function getPlatformConfig()
    {
        $config = new PlatformConfiguration();
        $config->setName($this->options['name']);
        $config->setFooter($this->options['footer']);
        $config->setSelfRegistration($this->options['allow_self_registration']);
        $config->setLocalLanguage($this->options['locale_language']);
        $config->setTheme($this->options['theme']);

        return $config;
    }

    protected function checkParameter($parameter)
    {
        if (!$this->hasParameter($parameter)) {
            throw new RuntimeException(
                "'{$parameter}' is not a parameter of the current platform configuration."
            );
        }
    }
}
