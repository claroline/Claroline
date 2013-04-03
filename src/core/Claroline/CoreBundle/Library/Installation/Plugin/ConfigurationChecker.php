<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Installation\Plugin\Configuration;
use Doctrine\ORM\EntityManager;

/**
 * Checker used to validate the configuration file of a plugin.
 */
class ConfigurationChecker implements CheckerInterface
{
    private $processedConfiguration;
    private $em;

    public function __construct(Yaml $yamlParser, EntityManager $em)
    {
        $this->yamlParser = $yamlParser;
        $this->em = $em;
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
        $listResource = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        foreach ($listResource as $resource) {
                $names[] = $resource->getName();
        }

        $listTool = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')->findAll();
        foreach ($listTool as $tool) {
                $tools[] = $tool->getName();
        }

        $processor = new Processor();
        $configuration = new Configuration($plugin, $names, $tools);

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