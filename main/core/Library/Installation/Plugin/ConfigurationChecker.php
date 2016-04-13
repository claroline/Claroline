<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Config\Definition\Processor;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Checker used to validate the configuration file of a plugin.
 *
 * @DI\Service("claroline.plugin.config_checker")
 */
class ConfigurationChecker implements CheckerInterface
{
    private $processedConfiguration;
    private $em;

    /**
     * @DI\InjectParams({
     *     "yamlParser" = @DI\Inject("claroline.symfony_yaml"),
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(Parser $yamlParser, EntityManager $em)
    {
        $this->yamlParser = $yamlParser;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param PluginBundle $plugin
     *
     * @todo Create dedicated repository methods to retrieve tool/type names
     */
    public function check(PluginBundle $plugin, $updateMode = false)
    {
        if (!is_file($plugin->getConfigFile())) {
            $error = new ValidationError('config.yml file missing');
            $errors = array($error);

            return $errors;
        }

        $config = $this->yamlParser->parse(file_get_contents($plugin->getConfigFile()));
        $names = array();
        $listResource = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        foreach ($listResource as $resource) {
            $names[] = $resource->getName();
        }

        $tools = array();
        /** @var \Claroline\CoreBundle\Entity\Tool\Tool[] $listTool */
        $listTool = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')->findAllWithPlugin();

        foreach ($listTool as $tool) {
            $toolPlugin = $tool->getPlugin();

            $tools[] = sprintf('%s%s', ($toolPlugin ? $toolPlugin->getBundleFQCN().'-' : ''), $tool->getName());
        }

        $resourceActions = array();
        $listResourceActions = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')->findBy(array('resourceType' => null, 'isCustom' => true));

        foreach ($listResourceActions as $resourceAction) {
            $resourceActions[] = $resourceAction->getName();
        }

        $widgets = array();
        /** @var \Claroline\CoreBundle\Entity\Widget\Widget[] $listWidget */
        $listWidget = $this->em->getRepository('ClarolineCoreBundle:Widget\Widget')->findAllWithPlugin();

        foreach ($listWidget as $widget) {
            $widgetPlugin = $widget->getPlugin();

            $widgets[] = sprintf('%s%s', ($widgetPlugin ? $widgetPlugin->getBundleFQCN().'-' : ''), $widget->getName());
        }

        $processor = new Processor();
        $configuration = new Configuration($plugin, $names, $tools, $resourceActions, $widgets);
        $configuration->setUpdateMode($updateMode);

        try {
            $this->processedConfiguration = $processor->processConfiguration($configuration, $config);
        } catch (\Exception $e) {
            $error = new ValidationError($e->getMessage());

            return array($error);
        }
    }

    public function getProcessedConfiguration()
    {
        return $this->processedConfiguration;
    }
}
