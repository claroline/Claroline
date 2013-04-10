<?php

namespace Claroline\CoreBundle\Library\Configuration;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Configuration\PlatformConfiguration;

/**
 * Service for accesssing/modifying the platform configuration parameters in prod/dev
 * environments. The service annotation cannot be used as the class is not the same
 * in test environment (see Library\Testing\PlatformTestConfigurationHandler).
 */
class PlatformConfigurationHandler
{
    private $configFile;
    private $options;

    public function __construct(array $configFiles)
    {
        $this->configFile = $configFiles['prod'];
        $this->options = Yaml::parse($this->configFile);
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
        $fileInfo = new \SplFileInfo($this->configFile);
        if (!$fileInfo->isWritable()) {
            $exception = new UnwritableException();
            $exception->setPath($this->configFile);
            throw $exception;
        }
        $this->checkParameter($parameter);
        $this->options[$parameter] = $value;
        file_put_contents($this->configFile, Yaml::dump($this->options));

    }

    protected function checkParameter($parameter)
    {
        if (!$this->hasParameter($parameter)) {
            throw new RuntimeException(
                "'{$parameter}' is not a parameter of the current platform configuration."
            );
        }
    }

    public function getPlatformConfig()
    {
        $platformConfig = new PlatformConfiguration(
            $this->getParameter('allow_self_registration'),
            $this->getParameter('locale_language'),
            $this->getParameter('theme')
        );

        return $platformConfig;
    }
}