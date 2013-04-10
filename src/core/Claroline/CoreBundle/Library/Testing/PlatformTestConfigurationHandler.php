<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * Service for accesssing/modifying the platform configuration parameters in test
 * environment (modification are stored in a separate file and merged with the prod/dev
 * configuration). The service annotation cannot be used as the service class is not the
 * same in other environments (see Library\Configuration\PlatformConfigurationHandler).
 */
class PlatformTestConfigurationHandler extends PlatformConfigurationHandler
{
    private $testConfigFile;
    private $testOptions;

    public function __construct(array $configFiles)
    {
        parent::__construct($configFiles);
        $this->testConfigFile = $configFiles['test'];
        $this->testOptions = Yaml::parse($this->testConfigFile);
    }

    public function getParameter($parameter)
    {
        $this->checkParameter($parameter);

        if (array_key_exists($parameter, $this->testOptions)) {
            return $this->testOptions[$parameter];
        }

        return parent::getParameter($parameter);
    }

    public function setParameter($parameter, $value)
    {
        $this->checkParameter($parameter);
        $this->testOptions[$parameter] = $value;
        file_put_contents($this->testConfigFile, Yaml::dump($this->testOptions));
    }

    public function eraseTestConfiguration()
    {
        file_put_contents($this->testConfigFile, Yaml::dump(array()));
        $this->testOptions = array();
    }
}