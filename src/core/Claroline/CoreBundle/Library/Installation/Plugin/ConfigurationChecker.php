<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Installation\Plugin\Configuration;

/**
 * Checker used to validate the configuration file of a plugin.
 */
class ConfigurationChecker implements CheckerInterface
{
    private $processedConfiguration;

    public function __construct(Yaml $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    /**
     * {@inheritDoc}
     *
     * @param PluginBundle $plugin
     */
    public function check(PluginBundle $plugin)
    {
        $this->plugin = $plugin;
        $config = $this->yamlParser->parse($plugin->getConfigFile());

        if (null == $config) {
            $error = new ValidationError('config.yml file missing');
            $errors = array($error);

            return $errors;
        }

        $processor = new Processor();
        $configuration = new Configuration($plugin);

        try {
            $processedConfiguration = $processor->processConfiguration($configuration, $config);
            $this->processedConfiguration = $processedConfiguration;
        } catch (\Exception $e) {
            $error = new ValidationError($e->getMessage());
            $errors = array($error);

            return $errors;
        }
    }

    public function getProcessedConfiguration()
    {
        return $this->processedConfiguration;
    }
}